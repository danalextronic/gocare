<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $__env->startSection('title'); ?><?php echo e(isset($title) ? $title : 'GoCare'); ?><?php $__env->stopSection(); ?>
    <title><?php echo $__env->yieldContent('title'); ?></title>
    <?php echo $__env->make('layouts.includes.head', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>

    <style>
        body {
            font-family: 'Lato';
        }

        .fa-btn {
            margin-right: 6px;
        }
    </style>

    <script>
        $(document).ready(function () {
            $(".datatable").DataTable();

            $(".confirm").click(function (e) {
                var c = confirm("This action cannot be undone! Are you sure you want to do this?");
                if (c) {
                    return true;
                } else {
                    return false;
                }
            });
        });
    </script>
</head>
<body id="app-layout">
<?php if(Session::has('error')): ?>
    <div class="alert alert-danger">
        <?php echo e(Session::get('error')); ?>

    </div>
<?php endif; ?>
<?php if(Session::has('info')): ?>
    <div class="alert alert-info">
        <?php echo e(Session::get('info')); ?>

    </div>
<?php endif; ?>
<?php $__env->startSection('nav'); ?>
    <?php echo $__env->make('layouts.includes.nav', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
<?php echo $__env->yieldSection(); ?>

<?php echo $__env->yieldContent('content'); ?>

</body>
</html>
