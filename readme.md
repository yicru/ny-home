## 概要
1ページのみなのでindexで完結
pc版のスタイルは
https://marukawa-reform.com/
こんな感じのデザインになる

トップページだけなので細かくいじるのはhome.phpのみ
cssはscssでページごとに出し分け(今回は1ページだけ)

## mp tool web
http://localhost/github/ny-home/mp/tool.html

## C:\Windows\System32\drivers\etc\hosts
127.0.0.1 ny-home.local

## C:\xampp\apache\conf\extra\httpd-vhosts.conf
<VirtualHost *:80>
    DocumentRoot "C:\xampp\htdocs\github\ny-home"
    ServerName nyhome.local
    <Directory "C:\xampp\htdocs\github\ny-home">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>