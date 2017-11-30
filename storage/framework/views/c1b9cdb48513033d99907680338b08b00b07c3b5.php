<p>Hi there!</p>

<p>We wanted to let you know there were some problems with your order import. Our system reported the following:</p>
    <pre><?php echo e($email_message); ?></pre>
<p>To view the errors, please login to our API portal manager by visiting the page below:</p>

<p><a href="<?php echo e(url('/orders/failed')); ?>">Failed orders</a></p>

<p>
    Sincerely,
<br /><br />
    Your friends at GoCare!
</p>
