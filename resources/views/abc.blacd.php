<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- <meta name="csrf-token" content="{{ csrf_token() }}"> --}}

    <title>Laravel Stripe Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://js.stripe.com/v3/"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
    
</head>
<body>
    <h1>Make a Payment</h1>
    <form id="payment-form">
        <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
        <div class="form-group">
            <label for="amount">Amount (in cents):</label>
            <input type="text" id="amount" name="amount" class="form-control form-control-sm" required>
        </div>
        <div>
            <label for="card-number">Card Number:</label>
            <div id="card-number"></div>
            <img id="card-icon" src="" alt="Card Icon" style="width: 30px; margin-left: 10px;">
        </div>
        <div>
            <label for="card-expiry">Expiration Date:</label>
            <div id="card-expiry"></div>
        </div>
        <div>
            <label for="card-cvc">CVC:</label>
            <div id="card-cvc"></div>
        </div>
        <button type="submit">Pay Now</button>
    </form>
   <script>




document.addEventListener('DOMContentLoaded', function () {
    const stripe = Stripe("{{ config('stripe.pk') }}");
    const elements = stripe.elements();

    const style = {
        base: {
            fontSize: '16px',
            color: '#32325d',
        },
    };

    const cardNumber = elements.create('cardNumber', { style: style });
    cardNumber.mount('#card-number');

    const cardExpiry = elements.create('cardExpiry', { style: style });
    cardExpiry.mount('#card-expiry');

    const cardCvc = elements.create('cardCvc', { style: style });
    cardCvc.mount('#card-cvc');

    const form = document.getElementById('payment-form');

    form.addEventListener('submit', async function (event) {
        event.preventDefault();

        // const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
        const csrfTokenElement = document.querySelector('#token').value;

        // console.log(csrfTokenElement)

        // const csrfToken = csrfTokenElement ? csrfTokenElement.content : null;
        const csrfToken = csrfTokenElement ? csrfTokenElement : null;


        const { paymentMethod, error } = await stripe.createPaymentMethod({
            type: 'card',
            card: cardNumber,
        });

        if (error) {
            console.error(error);
        } else {
            // Submit the form with the PaymentMethod ID
            const amount = document.getElementById('amount').value;
            handlePayment(paymentMethod.id, amount, csrfToken);
        }
    });

    async function handlePayment(paymentMethodId, amount, csrfToken) {
        try {
            const response = await fetch('/create-payment-intent', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    payment_method: paymentMethodId,
                    amount: amount,
                }),
            });

            const { client_secret } = await response.json();

            const result = await stripe.confirmCardPayment(client_secret, {
                payment_method: paymentMethodId,
            });

            if (result.error) {
                console.error(result.error);
            } else {
                // Payment succeeded
                alert('Payment succeeded!');
            }
        } catch (error) {
            console.error(error);
        }
    }

    cardNumber.on('change', function (event) {
                const cardIcon = document.getElementById('card-icon');
                cardIcon.src = `https://js.stripe.com/v3/elements-icons/${event.brand}.svg`;
            });
});


    </script>
</body>
</html>
