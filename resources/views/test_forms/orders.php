
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Claims Form Test</title>
    <script src="https://code.jquery.com/jquery-2.1.4.min.js" type="text/javascript"></script>
</head>

<body>

    <form id="orders_form">
        <p>
            <input type="text" name="serial_number" placeholder="serial_number">
        </p>
        <p>
            <input type="text" name="email" placeholder="email">
        </p>
        <p>
            <input type="text" name="sku" placeholder="sku">
        </p>
        <p>
            <input type="date" name="start_date" placeholder="start_date">
        </p>

        <p>
            <button id="submit">Submit</button>
        </p>
    </form>

    <script>
        $(document).ready(function() {
            $("#orders_form").on("submit", function(e) {
                e.preventDefault();
                console.log($("#orders_form").serialize());
                $.ajax({
                    'type': 'post',
                    'url' : '/orders',
                    'data': $("#orders_form").serialize(),
                    'success': function(msg) {
                        console.log(msg);
                    }

                });
            });
        });
    </script>


</body>
</html>
