# Bookings

Each booking submitted through the site is saved here as a JSON file named after
its reference number (e.g. `DCS12345.json`). This folder ships empty — live
customer records are not included in the source bundle.

Example of the structure the application writes:

```json
{
    "bookingReference": "DCS12345",
    "customerName": "Jane Doe",
    "customerEmail": "jane@example.com",
    "customerPhone": "000-000-0000",
    "pickup": "123 Example St, Brooklyn, NY",
    "dropoff": "JFK Airport, Queens, NY",
    "date": "2026-01-01",
    "timeSlot": "morning",
    "tripType": "oneway",
    "vehicleType": "full_size_car",
    "extraStops": 0,
    "totalPrice": 85.00,
    "paymentMethod": "driver",
    "paymentStatus": "pending",
    "timestamp": "2026-01-01 09:00:00"
}
```

The folder must remain writable by the web server so new bookings can be saved.
