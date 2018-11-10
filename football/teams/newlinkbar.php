<div class="row justify-content-center col">
    <?php
    for ($yearloop = 0; $yearloop < count($champyear); $yearloop++) {
        ?>
        <div class="card">
            <img src="/images/greystone15-2a.jpg" height="100">
            <span class="text-center"><?= $champyear[$yearloop] ?></span>
        </div>
    <?php } ?>
</div>
<hr/>

<ul class="nav nav-pills nav-fill col my-2 py-1">
    <li class="nav-item col-4"><a class="nav-link <?= ($page == "History") ? "active" : "" ?>"
                                  href="teamhistory.php?viewteam=<? print $viewteam; ?>">History</a></li>
    <li class="nav-item col-4"><a class="nav-link <?= ($page == "Roster") ? "active" : "" ?> "
                                  href="teamroster.php?viewteam=<? print $viewteam; ?>">Roster</a></li>
    <li class="nav-item col-4"><a class="nav-link <?= ($page == "Schedule") ? "active" : "" ?>"
                                  href="teamschedule.php?viewteam=<? print $viewteam; ?>">Schedule</a></li>
</ul>



