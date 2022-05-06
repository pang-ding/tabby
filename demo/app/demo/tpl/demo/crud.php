<?php $this->layout('demo/common/main')?>
<?= $this->insert('demo/common/header') ?>
<!-- Main content -->
<div class="container-fluid">
  <div class="row">
    <?= $this->insert('demo/common/nav') ?>
    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
      <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">CRUD (Mysql)</h1>
      </div>
      <?php if (!$dbStatus['conn']): ?>
      <div class="jumbotron jumbotron-fluid py-3">
        <div class="container mx-3">
          <h5 class="text-danger">请先修改: /conf/app_demo.ini 中 Mysql 配置 : (不要使用重要的库)</h5>
          <pre><code class="language-ini">mysql.host     = "<?= \T::$Conf->get('mysql.host', '') ?>"
mysql.username = "<?= \T::$Conf->get('mysql.username', '') ?>"
mysql.password = "<?= \T::$Conf->get('mysql.password', '') ?>"
mysql.port     = "<?= \T::$Conf->get('mysql.port', '') ?>"
mysql.dbname   = "<?= \T::$Conf->get('mysql.dbname', '') ?>"</code></pre>
          <div class="alert alert-danger" role="alert">
            连接异常: <?= $dbStatus['connError'] ?>
          </div>
        </div>
      </div>
      <?php elseif (!$dbStatus['tables']): ?>
      <div class="jumbotron jumbotron-fluid py-3">
        <div class="container mx-3">
          <h5 class="text-danger">创建 Demo 表, 请确认 : </h5>
          <pre><code class="language-sql"><?= $dbStatus['createSql'] ?></code></pre>
          <a href="/demo/crud/index?create_tables=1" class="btn btn-success">创建 Demo 表</a>
        </div>
      </div>
      <?php else: ?>
      <?= $this->insert('demo/list', ['tags'=>$tags]) ?>
      <?php endif ?>
    </main>
  </div>
</div>
</section>
<script>
  hljs.highlightAll()
</script>
<!-- /.content -->