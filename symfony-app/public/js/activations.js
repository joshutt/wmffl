/**
 * The lineup form's browser side: drag a player between Starters and
 * Reserves, and live position counters.
 *
 * Both are progressive enhancement. The checkbox inside each row is the
 * only thing that gets posted, and it stays the source of truth: a drop
 * ticks or unticks it, and ticking it moves the row. With JavaScript off
 * (or SortableJS blocked) the form behaves exactly as it always has -
 * submit, get the error list back, keep your ticks - because the server
 * validates every submission through App\Model\LineupRules regardless.
 *
 * The limits come from the page's data-lineup-rules attribute, emitted
 * from the same LineupRules object the server validates with, so the
 * browser cannot invent its own rules.
 *
 * This file replaces /base/js/activations.js, whose swapActivations()
 * had been AJAXing at a weekSubAct.php that does not exist.
 */
(function () {
    'use strict';

    var root = document.getElementById('lineup');
    var output = document.getElementById('lineup-counters');
    if (!root || !output) {
        return;
    }

    var rules;
    try {
        rules = JSON.parse(root.getAttribute('data-lineup-rules') || '{}');
    } catch (e) {
        return;
    }
    if (!rules.positions) {
        return;
    }

    var starters = document.getElementById('lineup-starters');
    var reserves = document.getElementById('lineup-reserves');
    var submit = document.getElementById('lineup-submit');
    var allowIllegal = document.getElementById('allowIllegal');

    // ---- counters ----

    function counts() {
        var byPos = {};
        Object.keys(rules.positions).forEach(function (pos) {
            byPos[pos] = 0;
        });

        // Locked starters have no checkbox - they ride along as hidden
        // inputs and still occupy their slot.
        root.querySelectorAll('.lineup-locked').forEach(function (input) {
            var pos = input.getAttribute('data-pos');
            if (pos in byPos) {
                byPos[pos] += 1;
            }
        });
        root.querySelectorAll('.lineup-check').forEach(function (input) {
            var pos = input.getAttribute('data-pos');
            if (input.checked && pos in byPos) {
                byPos[pos] += 1;
            }
        });

        return byPos;
    }

    function render() {
        var byPos = counts();
        var legal = true;
        var parts = [];

        Object.keys(rules.positions).forEach(function (pos) {
            var limit = rules.positions[pos];
            var n = byPos[pos];
            var ok = n >= limit.min && n <= limit.max;
            legal = legal && ok;
            var target = limit.min === limit.max ? limit.min : limit.min + '-' + limit.max;
            parts.push(
                '<span class="badge badge-' + (ok ? 'secondary' : 'danger') + ' p-2 m-1">' +
                pos + ' ' + n + '/' + target + '</span>'
            );
        });

        if (rules.flexTotal !== null && rules.flexGroup && rules.flexGroup.length) {
            var flex = 0;
            rules.flexGroup.forEach(function (pos) {
                flex += byPos[pos] || 0;
            });
            var flexOk = flex === rules.flexTotal;
            legal = legal && flexOk;
            parts.push(
                '<span class="badge badge-' + (flexOk ? 'secondary' : 'danger') + ' p-2 m-1">' +
                rules.flexGroup.join('+') + ' ' + flex + '/' + rules.flexTotal + '</span>'
            );
        }

        output.innerHTML = parts.join('');
        updateEmptyMessages();

        if (submit) {
            // The commissioner override is allowed to save an illegal
            // lineup on purpose, so never gate the button there.
            submit.disabled = !legal && !(allowIllegal && allowIllegal.checked);
        }
    }

    /** "Nobody is activated yet" belongs to an empty list, and only then. */
    function updateEmptyMessages() {
        root.querySelectorAll('.lineup-empty').forEach(function (message) {
            var list = document.getElementById(message.getAttribute('data-list'));
            message.hidden = !list || list.children.length > 0;
        });
    }

    // ---- keeping both lists in position order ----

    var posRank = {};
    Object.keys(rules.positions).forEach(function (pos, i) {
        posRank[pos] = i;
    });

    // A stable last resort when two players share a surname; the roster
    // query does not order beyond the name either.
    root.querySelectorAll('.lineup-row').forEach(function (row, i) {
        row.dataset.order = i;
    });

    function rank(row) {
        var pos = row.getAttribute('data-pos');
        return posRank[pos] === undefined ? Object.keys(posRank).length : posRank[pos];
    }

    /**
     * Put a list back in lineup order: HC, QB, RB, ... DB, and by
     * surname within a position - the same "ORDER BY p.pos, p.lastname"
     * the roster query used. Where a player was dropped decides which
     * list he is in, never where he sits in it.
     *
     * The surname comes from data-sortname rather than the visible
     * label, which reads "First Last" and would sort differently.
     *
     * Rows that are not players - the acting-head-coach picker - are
     * left alone, so the picker keeps its place at the top.
     */
    function sortList(list) {
        var rows = Array.prototype.slice.call(list.querySelectorAll('.lineup-row'));
        rows.sort(function (a, b) {
            var byPos = rank(a) - rank(b);
            if (byPos !== 0) {
                return byPos;
            }
            var nameA = a.getAttribute('data-sortname') || '';
            var nameB = b.getAttribute('data-sortname') || '';
            if (nameA !== nameB) {
                return nameA < nameB ? -1 : 1;
            }
            return Number(a.dataset.order) - Number(b.dataset.order);
        });
        rows.forEach(function (row) {
            list.appendChild(row);
        });
    }

    function sortLists() {
        sortList(starters);
        sortList(reserves);
    }

    /**
     * Everything that has to happen after a player changes list, in one
     * place so no code path can do half of it: put both lists back in
     * order, make the checkboxes match, redraw the counters.
     */
    function settle() {
        sortLists();
        syncChecks();
        render();
    }

    /**
     * The same, but a tick later. Sortable notes which inputs were
     * checked when a drag started and force-checks them again in its own
     * cleanup (_nulling(), Sortable 1.15), which runs after onEnd in the
     * same call stack. Unchecking a player dragged out of Starters
     * therefore cannot be done inside a drag callback - the library
     * would put the tick straight back. Do not "simplify" this to a
     * synchronous call.
     */
    var settlePending = false;
    function settleSoon() {
        if (settlePending) {
            return;
        }
        settlePending = true;
        setTimeout(function () {
            settlePending = false;
            settle();
        }, 0);
    }

    // ---- drag and drop ----

    /** Move a row into a list, if it is not already there. */
    function place(row, list) {
        if (row.parentElement !== list) {
            list.appendChild(row);
        }
    }

    /**
     * Make every checkbox agree with the list its row is sitting in.
     * List membership is what the owner sees, so it wins.
     */
    function syncChecks() {
        root.querySelectorAll('.lineup-row').forEach(function (row) {
            var check = row.querySelector('.lineup-check');
            if (check) {
                check.checked = (row.parentElement === starters);
            }
        });
    }

    function enableDragging() {
        if (!starters || !reserves || typeof Sortable === 'undefined') {
            return;
        }

        // A fresh options object per list: Sortable keeps the one it is
        // given on the instance, so the two must not share it.
        function options() {
            return {
                group: 'lineup',
                handle: '.lineup-grip',
                // A locked player cannot change state, so he cannot move
                filter: '.lineup-row-locked, .lineup-row-fixed',
                // Without this, Sortable calls preventDefault() on the
                // mousedown/touchstart that starts on a filtered row -
                // which is also how the acting-HC checkbox and <select>
                // get their clicks, so the dropdown would never open.
                preventOnFilter: false,
                animation: 150,
                // On touch, wait a moment before starting a drag so that
                // a swipe scrolls the page instead of picking up a player
                delay: 200,
                delayOnTouchOnly: true,
                ghostClass: 'lineup-ghost',
                chosenClass: 'lineup-chosen',
                dragClass: 'lineup-drag',
                onStart: function () {
                    starters.classList.add('lineup-list-active');
                    reserves.classList.add('lineup-list-active');
                },
                onAdd: settleSoon,
                onRemove: settleSoon,
                onUpdate: settleSoon,
                onSort: settleSoon,
                onEnd: function (event) {
                    starters.classList.remove('lineup-list-active');
                    reserves.classList.remove('lineup-list-active');
                    place(event.item, event.to);
                    settleSoon();
                }
            };
        }

        Sortable.create(starters, options());
        Sortable.create(reserves, options());

        // The rows really are draggable now, so say so
        root.querySelectorAll('.lineup-row:not(.lineup-row-locked)').forEach(function (row) {
            row.classList.add('lineup-draggable');
        });
        root.querySelectorAll('.lineup-hint').forEach(function (hint) {
            hint.classList.remove('d-none');
        });
    }

    // Ticking a box is the other way to do the same thing: keep the row
    // in the list its checkbox says it belongs to.
    root.addEventListener('change', function (event) {
        var check = event.target;
        if (check.classList.contains('lineup-check') && starters && reserves) {
            var row = check.closest('.lineup-row');
            if (row) {
                place(row, check.checked ? starters : reserves);
                sortLists();
            }
        }
        render();
    });

    if (allowIllegal) {
        allowIllegal.addEventListener('change', render);
    }

    // Whatever else happened, what gets posted is what the two lists
    // show. Cheap insurance against any library that decides to tidy up
    // our checkboxes behind us.
    var form = root.closest('form');
    if (form) {
        form.addEventListener('submit', syncChecks);
    }

    enableDragging();
    render();
})();
