<?php $__env->startSection('content'); ?>
    <div class="container spark-screen">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Orders</div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <p>
                                    <a href="<?php echo e(url('/orders/download/failed')); ?>" class="btn btn-primary">Download Failed Orders CSV</a>
                                </p>

                            </div>
                        </div>

                        <table class="table table-bordered table-striped table-hover datatable">
                            <thead>
                            <tr>
                                <th>Email</th>
                                <th>Warranty Sku</th>
                                <th>Sku</th>
                                <th>Serial Number</th>
                                <th>Activation Date</th>
                                <th>Created</th>
                                <th>Updated</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach($orders as $order): ?>
                                <tr>
                                    <td><?php echo e($order->email); ?></td>
                                    <td><?php echo e($order->warranty_sku); ?></td>
                                    <td><?php echo e($order->sku); ?></td>
                                    <td><?php echo e($order->serial_number); ?></td>
                                    <td><?php echo e($order->start_date); ?></td>
                                    <td><?php echo e($order->created_at); ?></td>
                                    <td><?php echo e($order->updated_at); ?></td>
                                    <td><?php echo e(($order->status === 'failed') ? 'FAILED: ' . $order->failed_reason : $order->status); ?></td>
                                    <td nowrap>
                                        <?php if($order->status === 'failed'): ?>
                                            <form action="<?php echo e(url('/orders/', [$order->id])); ?>" method="post">
                                                <?php echo e(csrf_field()); ?>

                                                <input type="hidden" name="_method" value="DELETE">
                                                <a href="<?php echo e(url('/orders/', [$order->id])); ?>" class="btn btn-info btn-sm">Edit</a>
                                                <input type="submit" value="Delete" class="confirm btn btn-danger btn-sm">

                                            </form>

                                        <?php else: ?>
                                            <a href="#" class="btn btn-info">View</a>
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