</div> <footer class="mt-auto py-3 bg-light border-top">
        <div class="container text-center">
            <span class="text-muted">SFMS &copy; <?php echo date('Y'); ?> - Group Dark Devils</span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
         $(document).ready(function() {
             // Initialize Select2 for elements with class 'select2-enable'
             $('.select2-enable').select2({
                 theme: 'bootstrap-5', // Use Bootstrap 5 theme
                 placeholder: $(this).data('placeholder') || "-- Select --", // Optional placeholder
                 allowClear: true
             });

             // Add other global JS initializations here if needed
         });
    </script>
    </body>
</html>