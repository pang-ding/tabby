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
      <div class="jumbotron jumbotron-fluid py-3">
        <div class="container mx-3">
          <h5 class="text-danger">请先修改: /conf/app_demo.ini 中 Mysql 配置 : </h5>
          <pre><code class="language-ini">mysql.host     = ""
mysql.username = ""
mysql.password = ""
mysql.port     = ""
mysql.dbname   = ""</code></pre>
        </div>
      </div>
      <div class="jumbotron jumbotron-fluid py-3">
        <div class="container mx-3">
          <h5 class="text-danger">创建 Demo 表, 请确认 : </h5>
          <pre><code class="language-sql">CREATE TABLE `tabby_demo_article` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `article_title` varchar(255) DEFAULT NULL,
  `article_content` varchar(255) DEFAULT NULL,
  `article_tag` varchar(16) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tabby_demo_tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag_title` varchar(32) DEFAULT NULL,
  `tag_key` varchar(16) DEFAULT NULL,
  `enable` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;</code></pre>
          <a href="/demo" class="btn btn-success">创建 Demo 表</a>
        </div>
      </div>
    </main>
  </div>
</div>
</section>
<script>
  hljs.highlightAll()
</script>
<!-- /.content -->