<!--&lt;!&ndash; Begin footer.php &ndash;&gt;-->
<!--<HR size = "1">-->
<!--<div class="footer">-->
<!--      <div class="text-small mb-1"><SUB>&copy; 1992-<?= date("Y") ?>. This web site and all-->
<!--        content on this site are property of the WMFFL and are intended for use by the members of this league and other-->
<!--        interested parties. All content may be republished, in whole or part,-->
<!--          as long as the original source is properly cited.</SUB></div>-->
<!--        <div class="align-content-center text-center"><img SRC="/images/flag_sm_anim.gif"></div>-->
<!--</div>-->
<!--    </TD></TR></TABLE>-->
<!--</BODY>-->
<!--</HTML>-->

<!--&lt;!&ndash; End footer.php &ndash;&gt;-->

<hr class="footerBar"/>


</div>

</main><!-- /.container -->

<footer class="page-footer font-small pt-4">
    <div class="text-center">
        <a class="navbar-brand" href="/"><img src="/images/test.png"></a>
    </div>
    <div class="footer-copyright text-center py-3">
        &copy; 1992-<?= date("Y") ?> WMFFL
    </div>
</footer>

<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<!--<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>-->
<!--<script>window.jQuery || document.write('<script src="/base/vendor/js/jquery-slim.min.js"><\/script>')</script>-->
<!--<script src="/base/vendor/js/popper.min.js"></script>-->
<!--<script src="/base/vendor/js/bootstrap.min.js"></script>-->
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
        integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN"
        crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"
        integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q"
        crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"
        integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl"
        crossorigin="anonymous"></script>

<?php
// Include any Javascript
if (isset($javascriptList)) {
    foreach ($javascriptList as $sheet) {
        print "<script src=\"$sheet\"></script>";
    }
}
?>
<script src="https://cdn.jsdelivr.net/npm/js-cookie@rc/dist/js.cookie.min.js"></script>

</body>
</html>