require('dotenv').config();
const express = require('express');
const stripe = require('stripe')(process.env.STRIPE_SECRET_KEY);
const mysql = require('mysql2/promise'); // MySQL support

const app = express();
const PORT = process.env.PORT || 3000;

// Database connection (match your PHP config)
const db = mysql.createPool({
    host: '127.0.0.1',
    user: 'root',
    password: '',
    database: 'eventini'
});

// Middleware
app.use(express.json());
app.use(express.static('public'));

// CORS (allow requests from PHP)
app.use((req, res, next) => {
    res.header('Access-Control-Allow-Origin', 'http://localhost');
    res.header('Access-Control-Allow-Headers', 'Content-Type');
    next();
});

// Stripe Checkout Endpoint
app.post('/create-checkout-session', async (req, res) => {
    try {
        const { eventId, tickets } = req.body;

        // 1. Verify ticket data
        if (!tickets || !Array.isArray(tickets)) {
            return res.status(400).json({ error: "Invalid ticket data" });
        }

        // 2. Prepare line items for Stripe
        const lineItems = tickets.map(ticket => ({
            price_data: {
                currency: 'usd',
                product_data: { name: ticket.ticket_type },
                unit_amount: Math.round(ticket.price * 100), // Convert to cents
            },
            quantity: ticket.quantity,
        }));

        // 3. Create Stripe session
        const session = await stripe.checkout.sessions.create({
            payment_method_types: ['card'],
            line_items: lineItems,
            mode: 'payment',
            success_url: `${req.headers.origin}/success.html?session_id={CHECKOUT_SESSION_ID}`,
            cancel_url: `${req.headers.origin}/cancel.html`,
        });

        // 4. Return session ID to frontend
        res.json({ id: session.id });

    } catch (error) {
        console.error("Stripe Error:", error);
        res.status(500).json({ error: error.message });
    }
});

// Start server
app.listen(PORT, () => console.log(`Server running on port ${PORT}`));