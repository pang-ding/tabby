<!DOCTYPE html>
<html lang="zh-CN">

<head>
  <meta charset="utf-8">
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
  <meta name="referrer" content="always">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="/static/favicon.ico">
  <title>Tabby Demo</title>
  <link rel="stylesheet" href="/static/bootstrap.min.css">
  <link rel="stylesheet" href="/static/highlight.js/solarized-light.css">

  <script src="/static/highlight.js/highlight.min.js"></script>
  <script src="/static/jquery.min.js"></script>
  <style>
    .bd-placeholder-img {
      font-size: 1.125rem;
      text-anchor: middle;
      -webkit-user-select: none;
      -moz-user-select: none;
      -ms-user-select: none;
      user-select: none;
    }

    @media (min-width: 768px) {
      .bd-placeholder-img-lg {
        font-size: 3.5rem;
      }
    }

    .nav-logo {
      width: 30px;
      height: 30px;
      margin-top: -2px;
    }
  </style>
</head>

<body class="bg-light">
  <!-- Body Wrapper -->
  <?=$this->section('content')?>
  <!-- ./body-wrapper -->
</body>

</html>