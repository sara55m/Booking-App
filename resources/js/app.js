import './bootstrap';

window.Echo.private('bookings')
    .listen('.payment.confirmed', (e) => {
        console.log('Payment confirmed:', e);

        alert(`Booking #${e.id} payment confirmed`);
    });
