
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Claims Form Test</title>
    <script src="https://code.jquery.com/jquery-2.1.4.min.js" type="text/javascript"></script>
</head>

<body>

    <form id="claims_form">
        <p>
            <input type="text" name="autoSerial" placeholder="serial_number">
        </p>
        <p>
            <input type="text" name="email" placeholder="email">
        </p>
        <p>
            <input type="text" name="phone" placeholder="phone">
        </p>
        <p>
            <input type="text" name="full_name" placeholder="full_name">
        </p>
        <p>
            <input type="text" name="address" placeholder="address">
        </p>
        <p>
            <input type="text" name="address2" placeholder="address2">
        </p>
        <p>
            <input type="text" name="city" placeholder="city">
        </p>
        <p>
            <input type="text" name="state" placeholder="state">
        </p>
        <p>
            <input type="text" name="zipcode" placeholder="zipcode"
        </p>
        <p>
            Question #20:<br />
            <input type="radio" name="question[20]" value="No">:No<br />
            <input type="radio" name="question[20]" value="Yes">:Yes<br />
        </p>
        <p>
            Question #12:<br />
            <textarea cols="40" rows="4" name="question[12]"></textarea>
        </p>
        <p>
            Question #6 (incurred date for not stolen device)
            <input type="date" name="question[6]">
        </p>
        <p>
            Question #14 (incurred date for stolen/lost device)
            <input type="date" name="question[14]">
        </p>

        <p>
            <button id="submit">Submit</button>
        </p>
    </form>

    <script>
        $(document).ready(function() {
            $("#claims_form").on("submit", function(e) {
                e.preventDefault();
                console.log($("#claims_form").serialize());
                $.ajax({
                    'type': 'post',
                    'url' : '/claims',
                    'data': $("#claims_form").serialize(),
                    'success': function(msg) {
                        console.log(msg);
                    }

                });
            });
        });
    </script>


</body>
</html>
