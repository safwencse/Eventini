document.addEventListener('DOMContentLoaded', function() {
    // Check URL for show=login parameter
    const params = new URLSearchParams(window.location.search);
    if (params.get("show") === "login") {
        document.getElementById('switch-to-login').click();
    }

    // Form switching functionality
    const switchToLogin = document.getElementById('switch-to-login');
    const switchToSignup = document.getElementById('switch-to-signup');
    const signupForm = document.querySelector('.signup-form');
    const loginForm = document.querySelector('.login-form');
    
    function switchForms(showForm, hideForm) {
        hideForm.classList.remove('active');
        setTimeout(() => {
            showForm.classList.add('active');
        }, 300);
    }

    if (switchToLogin && switchToSignup) {
        switchToLogin.addEventListener('click', (e) => {
            e.preventDefault();
            switchForms(loginForm, signupForm);
            history.pushState(null, '', '?show=login');
        });

        switchToSignup.addEventListener('click', (e) => {
            e.preventDefault();
            switchForms(signupForm, loginForm);
            history.pushState(null, '', window.location.pathname);
        });
    }

    // Password toggle functionality
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const passwordInput = this.closest('.password-group').querySelector('input');
            const icon = this.querySelector('i');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    });

    // Register form submission
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            resetErrors();

            // Show loading state
            const btn = this.querySelector('.btn-signup');
            const btnOriginalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Signing Up...';

            try {
                const formData = new FormData(this);
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData
                });

                // First check if the response is JSON
                let result;
                try {
                    result = await response.json();
                } catch (error) {
                    throw new Error('Server returned invalid JSON');
                }

                if (result.success) {
                    showSuccessAnimation();
                    setTimeout(() => {
                        window.location.href = result.redirect || 'profile.php';
                    }, 1500);
                } else {
                    if (result.errors) {
                        displayFormErrors(result.errors);
                    } else {
                        showGeneralError(result.message || 'An error occurred during registration');
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                showGeneralError('An unexpected error occurred. Please try again.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = btnOriginalText;
            }
        });
    }

    // Login form submission
    const loginFormElement = document.getElementById('loginForm');
    if (loginFormElement) {
        loginFormElement.addEventListener('submit', async function(e) {
            e.preventDefault();
            resetLoginErrors();

            // Show loading state
            const btn = this.querySelector('.btn-login');
            const btnOriginalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Logging In...';

            try {
                const formData = new FormData(this);
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData
                });

                // Check if response is JSON
                let result;
                try {
                    result = await response.json();
                } catch (error) {
                    throw new Error('Server returned invalid JSON');
                }

                if (result.success) {
                    window.location.href = result.redirect || 'profile.php';
                } else {
                    if (result.errors) {
                        displayLoginErrors(result.errors);
                    } else {
                        showLoginGeneralError(result.message || 'An error occurred during login');
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                showLoginGeneralError('An unexpected error occurred. Please try again.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = btnOriginalText;
            }
        });
    }

    // Helper functions
    function resetErrors() {
        // Reset register form errors
        document.querySelectorAll('#registerForm .is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
        document.querySelectorAll('#registerForm .invalid-feedback').forEach(el => {
            el.style.display = 'none';
            el.textContent = '';
        });
        document.getElementById('general-error').style.display = 'none';
    }

    function resetLoginErrors() {
        // Reset login form errors
        document.querySelectorAll('#loginForm .is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
        document.querySelectorAll('#loginForm .invalid-feedback').forEach(el => {
            el.style.display = 'none';
            el.textContent = '';
        });
        document.getElementById('login-general-error').style.display = 'none';
    }

    function displayFormErrors(errors) {
        for (const [field, message] of Object.entries(errors)) {
            const fieldElement = document.getElementById(field);
            const errorElement = document.getElementById(`${field}-error`);
            
            if (fieldElement && errorElement) {
                fieldElement.classList.add('is-invalid');
                errorElement.textContent = message;
                errorElement.style.display = 'block';
            } else {
                showGeneralError(message);
            }
        }
    }

    function displayLoginErrors(errors) {
        for (const [field, message] of Object.entries(errors)) {
            const fieldId = `login-${field.replace('_', '-')}`;
            const fieldElement = document.getElementById(fieldId);
            const errorElement = document.getElementById(`${fieldId}-error`);
            
            if (fieldElement && errorElement) {
                fieldElement.classList.add('is-invalid');
                errorElement.textContent = message;
                errorElement.style.display = 'block';
            } else {
                showLoginGeneralError(message);
            }
        }
    }

    function showGeneralError(message) {
        const generalError = document.getElementById('general-error');
        generalError.textContent = message;
        generalError.style.display = 'block';
    }

    function showLoginGeneralError(message) {
        const generalError = document.getElementById('login-general-error');
        generalError.textContent = message;
        generalError.style.display = 'block';
    }

    function showSuccessAnimation() {
        // Confetti animation
        confetti({
            particleCount: 150,
            spread: 70,
            origin: { y: 0.6 },
            colors: ['#1065ab', '#ff5900', '#a5e83b']
        });
    }

    // Initialize with signup form active if no show=login parameter
    if (!params.get("show") === "login" && signupForm) {
        signupForm.classList.add('active');
    }
});