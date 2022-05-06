<?php $this->layout('demo/common/main')?>
<?= $this->insert('demo/common/header') ?>
<!-- Main content -->
<div class="container-fluid">
    <div class="row">
        <?= $this->insert('demo/common/nav') ?>

        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">


            <div class="alert alert-danger bg-white mt-5" role="alert">
                <h4 class="alert-heading">Error - <?= $errorData['err_code'] ?>
                </h4>
                <hr>
                <p class="text-danger"><?= $errorData['err_msg'] ?>
                </p>
                <hr>
                <p class="mb-0">Tabby Exception 按照是否将错误信息反馈给用户分为两大类. 不能展示给用户的信息记录在 Log 中.</p>
                <p class="mb-0">开发模式下, 可以通过安装 "Chrome Logger" 在控制台看到当前请求产生的日志.</p>
                <p class="mb-0">开发模式下, 可以选择在错误页面输出异常执行栈.</p>
                <p class="mb-0">更多异常相关的信息请查阅文档.</p>
            </div>

            <div class="row">
                <div class="col-12">
                    <a href="#" class="btn btn-danger float-right" onclick="history.back()">
                        <i class="fas fa-angles-left"></i> 返回 </a>
                </div>
            </div>
            <?php if (isset($exception)) { ?>
            <!-- debug -->
            <div class="card card-danger mt-5">
                <div class="card-header text-danger">
                    <strong>调试信息</strong>
                </div>
                <div class="card-body pad table-responsive">
                    <h6 class="text-danger"><?= $exception->getMessage() ?>
                    </h6>
                    <pre
                        style="white-space: pre-wrap; word-wrap: break-word;"><?= $exception ?></pre>
                </div>
            </div>
            <!-- /.debug -->
            <?php } ?>
            <!-- /.error-page -->
        </main>
    </div>
</div>
</section>