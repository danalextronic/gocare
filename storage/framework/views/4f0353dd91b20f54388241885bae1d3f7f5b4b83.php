<?php $__env->startSection('content'); ?>
    <div class="container spark-screen">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">Import CSV</div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-5">
                                <form action="<?php echo e(url('/orders/imports')); ?>" method="POST" enctype="multipart/form-data">

                                    <?php echo csrf_field(); ?>


                                    <div class="form-group">
                                        <label>CSV</label>
                                        <input type="file" name="file" class="form-control">
                                    </div>

                                    <div class="form-group">
                                        <button href="#" class="btn btn-primary">Submit</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>