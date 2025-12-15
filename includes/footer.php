    <!-- Toast Container for Notifications -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 11000;">
        <div id="successToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <strong>✓</strong> <span id="successToastMessage"></span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
        
        <div id="errorToast" class="toast align-items-center text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <strong>✗</strong> <span id="errorToastMessage"></span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <footer class="footer py-5 mt-auto">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-3 mb-md-0">
                    <h5 class="text-white mb-3">Campus Marketplace</h5>
                    <p class="text-white-50 small">Your trusted platform for buying and selling second-hand items within the university community.</p>
                </div>
                <div class="col-md-4 mb-3 mb-md-0">
                    <h6 class="text-white mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="/" class="text-white-50 text-decoration-none small">Home</a></li>
                        <li><a href="/pages/listings.php" class="text-white-50 text-decoration-none small">Browse Listings</a></li>
                        <?php if (isset($isLoggedIn) && $isLoggedIn): ?>
                            <li><a href="/pages/profile.php" class="text-white-50 text-decoration-none small">My Profile</a></li>
                        <?php else: ?>
                            <li><a href="/pages/login.php" class="text-white-50 text-decoration-none small">Login</a></li>
                            <li><a href="/pages/register.php" class="text-white-50 text-decoration-none small">Register</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h6 class="text-white mb-3">Support</h6>
                    <ul class="list-unstyled">
                        <li><a href="/pages/create-complaint.php" class="text-white-50 text-decoration-none small">Report Issue</a></li>
                        <li><a href="#" class="text-white-50 text-decoration-none small">Terms of Service</a></li>
                        <li><a href="#" class="text-white-50 text-decoration-none small">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>
            <hr class="my-4 bg-white-50">
            <div class="text-center text-white-50 small">
                &copy; <?php echo date('Y'); ?> Campus Second-Hand Marketplace. Made with ❤️ for students.
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button id="backToTop" class="btn btn-primary" style="display: none; position: fixed; bottom: 30px; right: 30px; z-index: 1000; border-radius: 50%; width: 50px; height: 50px; box-shadow: 0 4px 12px rgba(0,0,0,0.3);" title="Back to top">
        ↑
    </button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    
    <script>
        // ========== TOAST NOTIFICATIONS ==========
        function showToast(type, message) {
            const toastElement = document.getElementById(type + 'Toast');
            const messageElement = document.getElementById(type + 'ToastMessage');
            
            if (toastElement && messageElement) {
                messageElement.textContent = message;
                const toast = new bootstrap.Toast(toastElement, { delay: 4000 });
                toast.show();
            }
        }
        
        // ========== SMOOTH SCROLL ==========
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
        
        // ========== AUTO-DISMISS ALERTS ==========
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        // ========== BACK TO TOP BUTTON ==========
        const backToTopBtn = document.getElementById('backToTop');
        
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopBtn.style.display = 'block';
            } else {
                backToTopBtn.style.display = 'none';
            }
        });
        
        backToTopBtn.addEventListener('click', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
        
        // ========== FORM VALIDATION ==========
        document.addEventListener('DOMContentLoaded', function() {
            // Client-side validation for all forms
            const forms = document.querySelectorAll('form.needs-validation');
            
            forms.forEach(form => {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                        showToast('error', 'Please fill in all required fields correctly.');
                    }
                    form.classList.add('was-validated');
                }, false);
            });
            
            // Real-time validation for email fields
            const emailInputs = document.querySelectorAll('input[type="email"]');
            emailInputs.forEach(input => {
                input.addEventListener('blur', function() {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (this.value && !emailRegex.test(this.value)) {
                        this.setCustomValidity('Please enter a valid email address');
                        this.classList.add('is-invalid');
                    } else {
                        this.setCustomValidity('');
                        this.classList.remove('is-invalid');
                        if (this.value) this.classList.add('is-valid');
                    }
                });
            });
            
            // Password strength indicator
            const passwordInputs = document.querySelectorAll('input[type="password"][name="password"]');
            passwordInputs.forEach(input => {
                input.addEventListener('input', function() {
                    const strength = checkPasswordStrength(this.value);
                    let helpText = this.nextElementSibling;
                    
                    if (!helpText || !helpText.classList.contains('password-strength')) {
                        helpText = document.createElement('small');
                        helpText.className = 'password-strength form-text';
                        this.parentNode.insertBefore(helpText, this.nextSibling);
                    }
                    
                    if (this.value.length > 0) {
                        helpText.textContent = 'Password strength: ' + strength.text;
                        helpText.style.color = strength.color;
                    } else {
                        helpText.textContent = '';
                    }
                });
            });
            
            // Character counter for textareas
            const textareas = document.querySelectorAll('textarea[maxlength]');
            textareas.forEach(textarea => {
                const maxLength = textarea.getAttribute('maxlength');
                const counter = document.createElement('small');
                counter.className = 'form-text text-muted text-end d-block';
                counter.textContent = `0 / ${maxLength} characters`;
                textarea.parentNode.insertBefore(counter, textarea.nextSibling);
                
                textarea.addEventListener('input', function() {
                    const currentLength = this.value.length;
                    counter.textContent = `${currentLength} / ${maxLength} characters`;
                    
                    if (currentLength > maxLength * 0.9) {
                        counter.style.color = '#ef4444';
                    } else {
                        counter.style.color = '#6b7280';
                    }
                });
            });
            
            // Real-time price formatting
            const priceInputs = document.querySelectorAll('input[name*="price"], input[name*="amount"]');
            priceInputs.forEach(input => {
                input.addEventListener('blur', function() {
                    if (this.value) {
                        const value = parseFloat(this.value);
                        if (!isNaN(value)) {
                            this.value = value.toFixed(2);
                        }
                    }
                });
            });
        });
        
        // Password strength checker
        function checkPasswordStrength(password) {
            let strength = 0;
            
            if (password.length >= 8) strength++;
            if (password.length >= 12) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;
            
            if (strength <= 2) return { text: 'Weak', color: '#ef4444' };
            if (strength <= 3) return { text: 'Medium', color: '#f59e0b' };
            if (strength <= 4) return { text: 'Strong', color: '#10b981' };
            return { text: 'Very Strong', color: '#059669' };
        }
        
        // ========== CONFIRMATION DIALOGS ==========
        document.addEventListener('click', function(e) {
            // Handle delete actions
            if (e.target.matches('[data-confirm]') || e.target.closest('[data-confirm]')) {
                const element = e.target.matches('[data-confirm]') ? e.target : e.target.closest('[data-confirm]');
                const message = element.getAttribute('data-confirm');
                
                if (!confirm(message)) {
                    e.preventDefault();
                    return false;
                }
            }
        });
        
        // ========== LOADING STATE ON FORM SUBMIT ==========
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn && !submitBtn.disabled) {
                    const originalText = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Loading...';
                    
                    // Re-enable after 5 seconds as fallback
                    setTimeout(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }, 5000);
                }
            });
        });
        
        // ========== DOUBLE-CLICK PROTECTION ==========
        let lastClickTime = 0;
        document.addEventListener('click', function(e) {
            if (e.target.matches('button[type="submit"]') || e.target.closest('button[type="submit"]')) {
                const currentTime = new Date().getTime();
                if (currentTime - lastClickTime < 1000) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
                lastClickTime = currentTime;
            }
        });
        
        // ========== AUTO-SAVE DRAFT (for long forms) ==========
        const longForms = document.querySelectorAll('form[data-autosave]');
        longForms.forEach(form => {
            const formId = form.getAttribute('id') || 'form_' + Math.random().toString(36).substr(2, 9);
            
            // Load saved data
            const savedData = localStorage.getItem('draft_' + formId);
            if (savedData) {
                try {
                    const data = JSON.parse(savedData);
                    Object.keys(data).forEach(key => {
                        const input = form.querySelector(`[name="${key}"]`);
                        if (input && !input.value) {
                            input.value = data[key];
                        }
                    });
                    showToast('success', 'Draft restored!');
                } catch (e) {
                    console.error('Failed to restore draft:', e);
                }
            }
            
            // Auto-save on change
            form.addEventListener('change', function() {
                const formData = new FormData(form);
                const data = {};
                for (let [key, value] of formData.entries()) {
                    data[key] = value;
                }
                localStorage.setItem('draft_' + formId, JSON.stringify(data));
            });
            
            // Clear draft on successful submit
            form.addEventListener('submit', function() {
                setTimeout(() => {
                    localStorage.removeItem('draft_' + formId);
                }, 1000);
            });
        });
    </script>
</body>
</html>


