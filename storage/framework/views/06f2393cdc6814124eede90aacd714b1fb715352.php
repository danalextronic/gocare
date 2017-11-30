<?php $__env->startSection('content'); ?>
    <div class="container spark-screen">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Imported CSVs</div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <p>
                                    <a href="<?php echo e(url('/orders/imports/create')); ?>" class="btn btn-primary">Import CSV</a>
                                </p>

                            </div>
                        </div>


                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>File Name</th>
                                <th>Status</th>
                                <th>Message</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach($imports as $import): ?>
                                <tr>
                                    <td><?php echo e($import->id); ?></td>
                                    <td><?php echo e($import->file_name); ?></td>
                                    <td><?php echo e($import->status); ?></td>
                                    <td><?php echo e($import->status_message); ?></td>
                                    <td>
                                        <a href="<?php echo e(url('/orders/imports/download/', [$import->id])); ?>" class="btn btn-info">Download</a>
                                        <?php if($import->status == 'failed'): ?>
                                        <a href="#">Errors</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>

                    </div>

                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>