#!/bin/sh

# (re)Installs nginx with concat module

if [ -n $(2>&1 nginx -V | tr -- - '\n' | grep concat) ]; then
    echo Nginx already has concat module
    exit;
fi

tmpDir=/tmp/nginx_install/
mkdir -p $tmpDir
cd $tmpDir

nxGit="https://github.com/nginx/nginx/archive/master.zip"
concatGit="https://github.com/alibaba/nginx-http-concat/archive/master.zip"

wget $nxGit -O nginx.zip
wget $concatGit -O concat.zip

unzip nginx.zip
unzip concat.zip

cd nginx-master/

_pkgname="nginx"
_user="www-data"
_group="www-data"
_doc_root="/usr/share/${_pkgname}/http"
_sysconf_path="etc"
_conf_path="${_sysconf_path}/${_pkgname}"
_tmp_path="/var/spool/${_pkgname}"
_pid_path="/run"
_lock_path="/var/lock"
_log_path="/var/log/${_pkgname}"

./configure \
    --prefix="/${_conf_path}" \
    --conf-path="/${_conf_path}/nginx.conf" \
    --sbin-path="/usr/bin/${_pkgname}" \
    --pid-path="${_pid_path}/${_pkgname}.pid" \
    --lock-path=${_pid_path}/${_pkgname}.lock \
    --http-client-body-temp-path=${_tmp_path}/client_body_temp \
    --http-proxy-temp-path=${_tmp_path}/proxy_temp \
    --http-fastcgi-temp-path=${_tmp_path}/fastcgi_temp \
    --http-uwsgi-temp-path=${_tmp_path}/uwsgi_temp \
    --http-scgi-temp-path=${_tmp_path}scgi_temp \
    --http-log-path=${_log_path}/access.log \
    --error-log-path=${_log_path}/error.log \
    --user=${_user} \
    --group=${_group} \
    --with-debug \
    --with-ipv6 \
    --with-imap \
    --with-imap_ssl_module \
    --with-http_ssl_module \
    --with-http_stub_status_module \
    --with-http_dav_module \
    --with-http_gzip_static_module \
    --with-http_realip_module \
    --with-http_addition_module \
    --with-http_xslt_module \
    --with-http_image_filter_module \
    --with-http_sub_module \
    --with-http_flv_module \
    --with-http_mp4_module \
    --with-http_random_index_module \
    --with-http_secure_link_module \
    --with-http_perl_module \
    --with-http_degradation_module \
    --with-http_geoip_module \
    --with-http_spdy_module \
    --with-http_gunzip_module \
    --add-module=../nginx-http-concat-master/

make
make install

rm -rf $tmpDir