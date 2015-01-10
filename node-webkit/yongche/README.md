~/dev/mac/node-webkit-v0.11.5-osx-x64/node-webkit.app/Contents/MacOS/node-webkit app.nw

http://peter.sh/experiments/chromium-command-line-switches/
https://github.com/rogerwang/node-webkit/wiki/Manifest-format

{
  "main": "index.html",
  "name": "nw-demo",
  "description": "demo app of node-webkit",
  "version": "0.1.0",
  "keywords": [ "demo", "node-webkit" ],
  "window": {
    "title": "node-webkit demo",
    "icon": "link.png",
    "toolbar": true,
    "frame": false,
    "width": 800,
    "height": 500,
    "position": "mouse",
    "min_width": 400,
    "min_height": 200,
    "max_width": 800,
    "max_height": 600
  },
  "chromium-args": "--touch-events --enabled --enable-touch-events --enable-pinch --enable-accelerated-compositing",
  "webkit": {
    "plugin": true
  }
}

必填字段

main

（字符串）当node-webkit打开时的默认页面

name

（字符串）包的名字，必须为独一无二的，可由字母，数字，下划线组成，不能有空格

功能性字段

nodejs

（布尔型）nodejs是否node-webkit中启用

node-main

（字符串）当node-webkit打开时的加载的node.js文件。可通过process.mainModule访问

Example：

index.html

    <html>
    <head>
        <title>Hello World!</title>
    </head>
    <body onload="process.mainModule.exports.callback0()">
        <h1>Hello World!</h1>
        We are using node.js <script>document.write(process.version); </script>
    </body>
    </html>
index.js

var i = 0;
exports.callback0 = function () {
    console.log(i + ": " + window.location);
    window.alert ("i = " + i);
    i = i + 1;
}
package.json

{
  "name": "nw-demo",
  "node-main": "index.js",
  "main": "index.html"
}
window

控制窗口的样子

webkit

控制webkit特性是否启用

窗口字段

title

（字符串）默认打开的窗口的名字

toolbar

（布尔值）是否显示工具栏

icon

（字符串）图标的路径

position

（字符串）只可能是这么几个值null center mouse。null指无定位，center指在显示器中间，mouse指在鼠标的位置

min_width/min_height

（整形）定义宽度和高度的最小值

resizable

（布尔值）窗口是否可调整大小

always-on-top

（布尔值）窗口是否总在最上

fullscreen

（布尔值）打开时是否全屏

frame

（布尔值）是否显示窗口框架

可以在代替框架的元素上添加css

.titlebar {
  -webkit-user-select: none;//禁止选中文字
  -webkit-app-region: drag;//拖动
}
show

（布尔值）是否在任务栏上显示

kiosk

（布尔值）是否处于kiosk状态，在kiosk状态下将全屏并且阻止用户关闭窗口