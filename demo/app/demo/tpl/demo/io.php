<?php $this->layout('demo/common/main')?>
<?= $this->insert('demo/common/header') ?>
<!-- Main content -->
<div class="container-fluid">
  <div class="row">
    <?= $this->insert('demo/common/nav') ?>

    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
      <div class="pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">输入输出</h1>
      </div>
      <div class="alert alert-primary " role="alert">
        <form class="form-inline" action="/demo/all/io">
          <div class="form-group">
            <label for="exampleInputEmail1">输入: </label>
            <input type="text" class="form-control mx-2" name="foo"
              value="<?= $foo ?>" placeholder="foo 2-10个字符">
            <input type="text" class="form-control mx-2" name="bar"
              value="<?= $bar ?>" placeholder="bar 1-100整数">
          </div>
          <button type="submit" class="btn btn-primary"> Submit </button>
        </form>
      </div>
      <div class="alert alert-primary" role="alert">
        输出: <?= $input ?>
      </div>
      <div class="alert alert-warning " role="alert">
        Tabby中预置了包括 IN / 枚举 / 数据合法性(Mysql | Mongo)... 在内的常用检查器, 可以直接使用.<br>
        当预置检查器不能满足需求时, 可以自定义 或 扩展默认检查器.<br>
        检查器支持数组参数. 附带格式化参数功能, 默认转义HTML实体, 当然也可以很方便的在局部或全局关闭此类功能.<br>
        另外报错信息以及其中的字段名也可以自定义.
      </div>

      <div class="pt-3 pb-2 mb-3 border-bottom">
        <h5>代码: </h5>
      </div>
      <div class="jumbotron jumbotron-fluid py-3 mt-3">
        <?= $this->getCode(Demo_AllController::class, 'ioAction') ?>
      </div>
    </main>
  </div>
</div>
</section>
<script>
  hljs.highlightAll()
</script>
<!-- /.content -->