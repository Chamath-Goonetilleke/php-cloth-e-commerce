<?php if (!isset($isLoginPage) || !$isLoginPage): ?>
    </div><!-- End of content-wrapper -->
    </main><!-- End of main-content -->
    </div><!-- End of admin-container -->
<?php endif; ?>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Chart.js for dashboard -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Admin scripts -->
<script src="<?php echo ADMIN_URL; ?>/assets/js/admin-scripts.js"></script>
<!-- Page specific scripts -->
<?php if (isset($additionalScripts)) echo $additionalScripts; ?>

</body>

</html><?php if (isset($_SESSION['temp_messages'])) unset($_SESSION['temp_messages']); ?>