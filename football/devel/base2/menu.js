       toc1on = new Image(107, 18);
       toc1on.src = "images/front_h.gif";
       toc1off = new Image(107, 18);
       toc1off.src = "images/front.gif";
       toc2on = new Image(107, 18);
       toc2on.src = "images/teams_h.gif";
       toc2off = new Image(107, 18);
       toc2off.src = "images/teams.gif";
       toc3on = new Image(107, 18);
       toc3on.src = "images/sched_h.gif";
       toc3off = new Image(107, 18);
       toc3off.src = "images/sched.gif";
       toc4on = new Image(107, 18);
       toc4on.src = "images/stand_h.gif";
       toc4off = new Image(107, 18);
       toc4off.src = "images/stand.gif";
       toc5on = new Image(107, 18);
       toc5on.src = "images/trans_h.gif";
       toc5off = new Image(107, 18);
       toc5off.src = "images/trans.gif";
       toc6on = new Image(107, 18);
       toc6on.src = "images/newlet_h.gif";
       toc6off = new Image(107, 18);
       toc6off.src = "images/newlet.gif";
       toc7on = new Image(107, 18);
       toc7on.src = "images/history_h.gif";
       toc7off = new Image(107, 18);
       toc7off.src = "images/history.gif";
       toc8on = new Image(107, 18);
       toc8on.src = "images/links_h.gif";
       toc8off = new Image(107, 18);
       toc8off.src = "images/links.gif";
       toc9on = new Image(107, 18);
       toc9on.src = "images/rules_h.gif";
       toc9off = new Image(107, 18);
       toc9off.src = "images/rules.gif";
       toc10on = new Image(107, 18);
       toc10on.src = "images/active_h.gif";
       toc10off = new Image(107, 18);
       toc10off.src = "images/active.gif";
       toc11on = new Image(107, 18);
       toc11on.src = "images/software_h.gif";
       toc11off = new Image(107, 18);
       toc11off.src = "images/software.gif";
      
      function img_act(imgName) {
               imgOn = "/images/" + imgName + "_h.gif";
               document [imgName].src = imgOn;
       }

       function img_inact(imgName) {
               imgOff = "/images/" + imgName + ".gif";
               document [imgName].src = imgOff;
       }
