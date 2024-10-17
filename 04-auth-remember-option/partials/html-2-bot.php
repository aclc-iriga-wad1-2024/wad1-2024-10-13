<?php if(!isset($view)) exit(); ?>
    </div>
    <script src="dist/bootstrap-5.2.3/js/bootstrap.bundle.min.js"></script>
    <script>
        // enable tooltips on Bootstrap
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    </script>
</body>
</html>