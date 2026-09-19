<?php
/**
 * HTML Footer Include
 * Mobile Shop Management System
 */
?>
<footer class="mt-auto py-4 bg-dark text-white-50 border-top">
    <div class="container text-center text-md-start">
        <div class="row align-items-center">
            <div class="col-md-6 mb-3 mb-md-0">
                <h6 class="text-white fw-bold mb-1">
                    <i class="bi bi-phone text-warning me-1"></i> <?= SITE_NAME ?>
                </h6>
                <small class="text-muted d-block">Premier Smartphone Retailer & Inventory Management System.</small>
            </div>
            <div class="col-md-6 text-md-end">
                <small class="d-block">&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All Rights Reserved.</small>
                <small class="text-muted">Quality Mobiles &bull; Genuine Warranty &bull; Fast Delivery</small>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<!-- Application Custom JS -->
<script src="<?= asset('js/script.js') ?>"></script>
</body>
</html>
