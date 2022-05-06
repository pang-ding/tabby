<div class="alert alert-primary" role="alert">
  这个实例涉及到 路由 / AOP / 验证器 / Model / Response(templet & json)... 等框架主要模块的使用
</div>
<div class="alert alert-warning" role="alert">
  请打开浏览器控制台查看 请求 / 响应 数据
</div>
<div class="form-group row">
  <div class="col-3">
    <input type="text" class="form-control" id="search" placeholder="搜索 (LIKE %title%)">
  </div>
  <div>
    <button type="submit" class="btn btn-primary" id="search_btn">Search</button>
  </div>
</div>
<div>
  <table class="table">
    <thead>
      <tr>
        <th scope="col" style="width: 120px;">#ID</th>
        <th scope="col">Title</th>
        <th scope="col">Content</th>
        <th scope="col">Tag</th>
        <th scope="col" style="width: 210px;"></th>
      </tr>
    </thead>
    <tbody>
      <tr class="table-warning">
        <td></td>
        <td><input type="text" class="form-control" id="title_create"></td>
        <td><input type="text" class="form-control" id="content_create"></td>
        <td>
          <select class="form-control" id="tag_create">
            <?php foreach ($tags as $k => $v):?>
            <option value="<?= $k ?>"><?= $v ?>
            </option>
            <?php endforeach ?>
          </select>
        </td>
        <td><button type="submit" class="btn btn-primary" id="create_btn">Create</button></td>
      </tr>
    </tbody>
    <tbody id="demolist">
    </tbody>
  </table>
</div>
<div id="pages"></div>
<div class="pt-3 pb-2 mb-3 border-bottom">
  <h5>代码: </h5>
</div>
<div class="jumbotron jumbotron-fluid py-3 mt-3">
  <?= $this->getCode(Demo_ArticleController::class) ?>
  <?= $this->getCode(\Rules\DemoRules::class) ?>
  <?= $this->getCode(\Mod\DemoArticleMod::class) ?>
  <?= $this->getCode(\Mod\DemoTagMod::class) ?>
  <?= $this->getCode(\Plugins\DemoPlugins::class, 'routerShutdown') ?>
</div>

<script>
  // 分页
  (function(factory) {
    if (typeof module === 'object' && typeof module.exports === 'object') {
      module.exports = factory(global.$)
    } else {
      factory($)
    }
  })(function($) {
    if ($ && $.fn) {
      $.fn.pagination = function({
        count,
        now,
        size,
        pageSize
      }, callback) {
        count = Number(count)
        now = Number(now)
        now = now > 0 ? now : 1
        size = Number(size)
        size = size > 0 ? size : 10
        pageSize = Number(pageSize)
        pageSize = pageSize > 0 ? pageSize : 20

        let pages = Math.ceil(count / pageSize)
        if (now > pages) {
          now = pages
        }
        let start = 1
        let end = size > pages ? pages : size
        if (pages > size && now - Math.floor(size / 2) + 1 > 1) {
          if (now - Math.floor(size / 2) + size > pages) {
            start = pages - size + 1
            end = pages
          } else {
            start = now - Math.floor(size / 2)
            end = start + size - 1
          }
        }

        let createItem = function(i, inner) {
          let item = $(
            '<li class="page-item"><a class="page-link" href="#">' +
            inner +
            '</a></li>'
          )
          if (i != now) {
            item.click(function() {
              callback(i)
            })
          } else {
            item.addClass(now == inner ? 'active' : 'disabled')
          }
          return item
        }

        let pagination = $('<ul class="pagination pagination-sm m-0"></ul>')
        pagination.append(
          $(
            '<li class="page-item disabled"><a class="page-link text-nowrap" href="#"> ' +
            count +
            ' 条 , ' +
            pages +
            ' 页(每页 ' + pageSize + ' 条)</a></li>'
          )
        )
        pagination.append(createItem(1, '&lt;&lt;'))
        pagination.append(
          createItem(
            now - 1 > 1 ? now - 1 : 1,
            '&lt;'
          )
        )
        for (let i = start; i <= end; i++) {
          pagination.append(createItem(i, i))
        }
        pagination.append(
          createItem(
            now + 1 > end ? end : now + 1,
            '&gt;'
          )
        )
        pagination.append(
          createItem(pages, '&gt;&gt;')
        )

        $(this).html(pagination)
      }
    }
  });

  (function() {
    let api = function(url, data, callback) {
      console.warn('[**DEMO**] JSON请求:', url, data)
      jQuery.ajax(url, {
        dataType: 'json',
        type: 'POST',
        data: data,
        success: function(data) {
          let errData = null
          if (!Object.isExtensible(data) || data.err_code === undefined) {
            console.error('[**DEMO**] 请求失败:', url)
          } else {
            if (data.err_code != 0) {
              console.error('[**DEMO**] 请求失败:', data.err_msg, data)
            } else {
              callback(data.data)
            }
          }
        },
        error: function(xhr, status, error) {
          console.error('[**DEMO**] 请求失败:', url)
        },
        async: true,
        timeout: 3000,
      })
    }

    $("#create_btn").click(function() {
      api(
        '/demo/article/create', {
          article_title: $("#title_create").val(),
          article_content: $("#content_create").val(),
          article_tag: $("#tag_create").val(),
        },
        (data) => {
          console.warn('[**DEMO**] Create返回:', data.rst)
          flushList()
        }
      )
    })

    $("#search_btn").click(function() {
      flushList()
    })

    window.flushList = function(num) {
      num = num || 1
      api(
        '/demo/article/list', {
          article_search: $("#search").val(),
          page_num: num,
          page_size: 5
        },
        (data) => {
          console.warn('[**DEMO**] 列表返回:', data.pageData)
          $("#pages").pagination({
            count: data.pageData.total,
            now: num,
            pageSize: 5,
          }, function(i) {
            $(location).attr("href", "javascript: flushList(" + i + ")")
          })
          $('#demolist').empty()
          $.each(data.pageData.list, (k, v) => {
            let tr = $('<tr></tr>')
            tr.append($('<td><input type="text" class="form-control" value="' + v.id + '" disabled></td>'))
            tr.append($('<td><input type="text" class="form-control" id="title_' + v.id + '" value="' + v
              .article_title + '"></td>'))
            tr.append($('<td><input type="text" class="form-control" id="content_' + v.id + '" value="' + v
              .article_content +
              '"></td>'))
            let tagSelect = $('<select class="form-control" id="tag_' + v.id +
              '"><?php foreach ($tags as $k => $v):?><option value="<?= $k ?>"><?= $v ?></option><?php endforeach ?></select>'
            )
            tagSelect.val(v.article_tag)
            tr.append($('<td></td>').append(tagSelect))
            let update = $('<button type="button" class="btn btn-primary mr-2">Update</button>')
            update.click(function() {
              api(
                '/demo/article/update', {
                  article_id: v.id,
                  article_title: $("#title_" + v.id).val(),
                  article_content: $("#content_" + v.id).val(),
                  article_tag: $("#tag_" + v.id).val(),
                },
                (data) => {
                  console.warn('[**DEMO**] Update返回:', data, '(Controller 用三种方式执行了3次更新操作)')
                  flushList()
                }
              )
            })
            let del = $('<button type="button" class="btn btn-danger">Delete</button>')
            del.click(function() {
              api(
                '/demo/article/delete', {
                  article_id: v.id,
                },
                (data) => {
                  console.warn('[**DEMO**] Delete返回:', data.rst)
                  flushList()
                }
              )
            })
            tr.append($('<td></td>').append(update).append(del))
            $('#demolist').append(tr)
          })
        })
    }
    flushList()
  })()
</script>