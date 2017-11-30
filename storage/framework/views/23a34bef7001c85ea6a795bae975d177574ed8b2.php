<nav class="navbar navbar-default">
    <div class="container">
        <div class="navbar-header">

            <!-- Collapsed Hamburger -->
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                    data-target="#spark-navbar-collapse">
                <span class="sr-only">Toggle Navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>

            <!-- Branding Image -->
            <a class="navbar-brand" href="<?php echo e(url('/')); ?>">
                GoCare
            </a>
        </div>

        <div class="collapse navbar-collapse" id="spark-navbar-collapse">
            <!-- Left Side Of Navbar -->
            <ul class="nav navbar-nav">
                <?php /*<li><a href="<?php echo e(url('/')); ?>">Home</a></li>*/ ?>
                <?php if(Auth::check()): ?>
                    <li><a href="<?php echo e(url('/apikeys')); ?>">API Key</a></li>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true"
                           aria-expanded="false">Orders <span class="caret"></span></a>
                        <ul class="dropdown-menu">
                            <li><a href="<?php echo e(url('/orders/create')); ?>">Create</a></li>
                            <li><a href="<?php echo e(url('/orders')); ?>">View</a></li>
                            <li><a href="<?php echo e(url('/orders/imports/')); ?>">Imports</a></li>
                        </ul>
                    </li>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"
                           aria-haspopup="true">Claims <span class="caret"></span></a>
                        <ul class="dropdown-menu">
                            <li><a href="<?php echo e(url('/claims/create')); ?>">Create</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
                <?php if (Gate::check('update', \App\User::class)): ?>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true"
                           aria-expanded="false">Users<span class="caret"></span></a>
                        <ul class="dropdown-menu">
                            <li><a href="<?php echo e(url('/users')); ?>">View</a></li>
                            <li><a href="<?php echo e(url('/users/create')); ?>">Add User</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>

            <!-- Right Side Of Navbar -->
            <ul class="nav navbar-nav navbar-right">
                <!-- Authentication Links -->
                <?php if(Auth::guest()): ?>
                    <?php /*<li><a href="<?php echo e(url('/login')); ?>">Login</a></li>*/ ?>
                    <?php /*<li><a href="<?php echo e(url('/register')); ?>">Register</a></li>*/ ?>
                <?php else: ?>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                           aria-expanded="false"><i class="fa fa-bell"></i> Alerts <span class="caret"></span></a>
                        <ul class="dropdown-menu" role="menu">
                            <li><a href="<?php echo e(url('/orders/failed')); ?>">Failed Orders</a></li>
                        </ul>
                    </li>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                            <?php echo e(Auth::user()->name); ?> <span class="caret"></span>
                        </a>

                        <ul class="dropdown-menu" role="menu">
                            <li><a href="<?php echo e(url('/logout')); ?>"><i class="fa fa-btn fa-sign-out"></i>Logout</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
