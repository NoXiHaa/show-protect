    </main>

    <!-- Footer -->
    <footer class="footer mt-auto py-4 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="mb-3">Thư Viện Phần Mềm</h5>
                    <p class="text-muted mb-0">
                        Kho tàng phần mềm đa dạng và phong phú, 
                        cung cấp những công cụ hữu ích cho người dùng.
                    </p>
                </div>
                <div class="col-md-3">
                    <h6 class="mb-3">Liên kết</h6>
                    <ul class="list-unstyled">
                        <li><a href="<?= url('trang-chu') ?>" class="text-decoration-none">Trang chủ</a></li>
                        <li><a href="<?= url('danh-muc') ?>" class="text-decoration-none">Danh mục</a></li>
                        <li><a href="<?= url('gioi-thieu') ?>" class="text-decoration-none">Giới thiệu</a></li>
                        <li><a href="<?= url('lien-he') ?>" class="text-decoration-none">Liên hệ</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6 class="mb-3">Theo dõi chúng tôi</h6>
                    <div class="social-links">
                        <a href="#" class="text-decoration-none me-3">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="#" class="text-decoration-none me-3">
                            <i class="bi bi-twitter"></i>
                        </a>
                        <a href="#" class="text-decoration-none">
                            <i class="bi bi-github"></i>
                        </a>
                    </div>
                </div>
            </div>
            <hr class="my-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0 text-muted">
                        © <?= date('Y') ?> Thư Viện Phần Mềm. All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <ul class="list-inline mb-0">
                        <li class="list-inline-item">
                            <a href="<?= url('dieu-khoan') ?>" class="text-muted text-decoration-none">
                                Điều khoản sử dụng
                            </a>
                        </li>
                        <li class="list-inline-item">
                            <span class="text-muted">·</span>
                        </li>
                        <li class="list-inline-item">
                            <a href="<?= url('bao-mat') ?>" class="text-muted text-decoration-none">
                                Chính sách bảo mật
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Scripts -->
    <?php if (isset($extra_scripts)): ?>
        <?= $extra_scripts ?>
    <?php endif; ?>
</body>
</html> 