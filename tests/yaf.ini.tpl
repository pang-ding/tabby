[conf]

;# SYSTEM
;项目名
prj.name = test
;项目路径
prj.path = HOME_PATH
;APP名
app.name = test
;APP路径
app.path = HOME_PATH "app/" APP_NAME "/"
;APP Title
app.title = TEST

;# Tabby
tabby.langPath = HOME_PATH "/language/zh/"
;模板路径
tabby.tplPath = HOME_PATH "app/" APP_NAME "/tpl/"
;#UPLOAD
tabby.uploadPath = HOME_PATH "static/"
;临时文件路径
tabby.tmpPath = HOME_PATH "tmp/"
;CLI模式
tabby.isCli = 0
;Debug模式
tabby.isDebug = false

;##ENV
;env.zdaemon = /usr/local/bin/zdaemon

;##LOG
log.level = info

;#YAF
;Bootstrap路径(绝对路径)
application.bootstrap = HOME_PATH "app/" APP_NAME "/" "Bootstrap.php"
;APP路径
application.directory = HOME_PATH "app/" APP_NAME "/"
;本地(自身)类库的绝对目录地址
application.library = HOME_PATH "src"
;APP模板路径
;application.tpl = HOME_PATH "app/" APP_NAME "/tpl/"
;在出错的时候, 是否抛出异常
application.dispatcher.throwException = true
;//是否使用默认的异常捕获Controller, 如果开启, 在有未捕获的异常的时候, 控制权会交给ErrorController的errorAction方法, 可以通过$request->getException()获得此异常对象
application.dispatcher.catchException = true
;启用SPL autoload
application.system.use_spl_autoload = false
;URI前缀
application.baseUri = "/"

;#MYSQL
mysql.host     = 
mysql.port     = 
mysql.username = 
mysql.password = 
mysql.dbname   = 

;#MONGO
mongo.host     = 
mongo.port     = 
mongo.username = 
mongo.password = 
mongo.dbname   = 

;##REDIS
redis.host = 
redis.port = 
redis.auth = 

[dev : conf]
mysql.host     = 
mysql.port     = 
mysql.username = 
mysql.password = 
mysql.dbname   = 

mongo.host     = 
mongo.port     = 
mongo.username = 
mongo.password = 
mongo.dbname   = 

redis.host = 
redis.port = 
redis.auth = 