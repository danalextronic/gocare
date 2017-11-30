<?php $__env->startSection('content'); ?>
    <div class="container spark-screen">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">API Key</div>

                    <div class="panel-body">
                        <?php if($apikey): ?>
                            Below you will find your API key, copy and paste it into your code to connect with the GoCare API:<br />
                            <input type="text" class="form-control" value="<?php echo e($apikey->secret); ?>">
                            <p>
                            <form action="/apikeys" method="POST">
                                <input type="submit" value="Create New API Key" class="btn btn-primary create-new-api-key">
                            </form>
                            </p>
                        <?php else: ?>
                        <div class="form-group">
                            <p>
                                You have not yet created an API key, please click the button below to create your new API key:
                                <form action="/apikeys" method="POST">
                                    <input type="submit" value="Create New API Key" class="btn btn-primary">
                                </form>
                            </p>

                        </div>


                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $(".create-new-api-key").click(function() {
                var c = confirm("This will generate a new API key and any of your applications using the current key will no longer be able to connect, are you sure?");
                if (c) {
                    return true;
                } else {
                    return false;
                }
            });
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>