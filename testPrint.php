<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Invoice Printer Test</title>
</head>
<body>
  <button onclick="sendToPrinter(30)">Print Invoice 30</button>

  <script>
    async function sendToPrinter(num) {
      try {
        const response = await fetch('http://localhost:8080/print-number', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            number: parseInt(num)
          })
        });

        if (!response.ok) {
          const errorData = await response.json();
          console.error('Server responded with an error:', errorData.message || response.statusText);
          alert(`Printing failed: ${errorData.message || response.statusText}`);
        } else {
          const successData = await response.json();
          console.log('Print successful:', successData);
          alert(`Print successful: ${successData.message}`);
        }

      } catch (error) {
        console.error('Error sending request to printer service:', error);
        alert(`Could not connect to printer service. Please ensure it's running. Error: ${error.message}`);
      }
    }
  </script>
</body>
</html>