document.addEventListener('DOMContentLoaded', function() {
    // Initialize Stripe with your publishable key
    const stripe = Stripe('pk_test_51REYH2FbYksDBRZfUnGj1GZCzFolIGhKd4rx7S5LABI15RgKhZdvYD7MTKCYFQignkoOul7YE7encCLrhz4UNaLt00OXMxgGNv');
    const payButton = document.getElementById('payButton');
    const payButtonText = document.getElementById('payButtonText');
    const payButtonSpinner = document.getElementById('payButtonSpinner');

    // Ticket quantity controls
    document.querySelectorAll('.increment').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentNode.querySelector('.ticket-quantity');
            input.value = parseInt(input.value) + 1;
            updateTotal();
        });
    });

    document.querySelectorAll('.decrement').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentNode.querySelector('.ticket-quantity');
            const newValue = parseInt(input.value) - 1;
            input.value = newValue >= 0 ? newValue : 0;
            updateTotal();
        });
    });

    // Manual input handling
    document.querySelectorAll('.ticket-quantity').forEach(input => {
        input.addEventListener('change', function() {
            this.value = parseInt(this.value) >= 0 ? parseInt(this.value) : 0;
            updateTotal();
        });
    });

    // Pay button click handler
    payButton.addEventListener('click', async function() {
        const total = calculateTotal();
        if (total > 0) {
            // Show loading state
            togglePayButtonState(true);

            try {
                // Create line items array for Stripe
                const lineItems = Array.from(document.querySelectorAll('.ticket-quantity'))
                    .filter(input => parseInt(input.value) > 0)
                    .map(input => ({
                        price: input.dataset.stripePriceId,
                        quantity: parseInt(input.value)
                    }));

                // Create checkout session
                const response = await fetch('http://localhost:3000/create-checkout-session', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ lineItems })
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.error || 'Payment failed. Please try again.');
                }

                const { id: sessionId } = await response.json();

                // Redirect to Stripe Checkout
                const { error } = await stripe.redirectToCheckout({ 
                    sessionId 
                });

                if (error) throw error;
            } catch (error) {
                console.error('Payment Error:', error);
                alert(`Payment failed: ${error.message}`);
                togglePayButtonState(false);
            }
        } else {
            alert('Please select at least one ticket');
        }
    });

    function togglePayButtonState(isLoading) {
        payButton.disabled = isLoading;
        payButtonText.textContent = isLoading ? 'Processing...' : 'Pay with Stripe';
        payButtonSpinner.classList.toggle('d-none', !isLoading);
    }

    function calculateTotal() {
        return Array.from(document.querySelectorAll('.ticket-quantity'))
            .reduce((total, input) => 
                total + (parseInt(input.value) * parseFloat(input.dataset.price)), 0);
    }

    function updateTotal() {
        document.getElementById('totalAmount').textContent = `$${calculateTotal().toFixed(2)}`;
    }
});