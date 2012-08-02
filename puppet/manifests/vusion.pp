Exec {
    path => ["/bin", "/usr/bin", "/usr/local/bin"],
    #user => 'root',
}


# TODO: use the apt module
exec { "apt-get update":
    command => "ls", #"apt-get update",
    user => "root"
}

# Install these packages after apt-get update
define apt::package($ensure='latest') {
    package { $name:
        ensure => $ensure,
        subscribe => Exec['apt-get update'];
    }
}

# Install Backend these packages
#apt::package { "build-essential": }
apt::package { "python": }
apt::package { "python-dev": }
apt::package { "python-setuptools": }
apt::package { "python-pip": }
apt::package { "python-virtualenv": }
#apt::package { "rabbitmq-server": ensure => "1.7.2-1ubuntu1" }
apt::package { "git-core": }
#apt::package { "openjdk-6-jre-headless": }
apt::package { "libcurl3": }
apt::package { "libcurl4-openssl-dev": }
#apt::package { "redis-server": }

# Install Frontend packages
apt::package { "mongodb-10gen": require => File["mongodb-apt-list"] }
#apt::package { "mysql-server": }
apt::package { "php5": }
apt::package { "php5-dev": }
#apt::package { "apache2": }
#apt::package { "libapache2-mod-php5": }

# Install packatge necessary for installation (to be removed at the end)
apt::package { "make": }
apt::package { "augeas-tools": require => Exec['ppa-augeas']}
apt::package { "libaugeas-dev": require => Exec['ppa-augeas']}
apt::package { "libaugeas0": require => Exec['ppa-augeas']}
apt::package { "augeas-lenses": require => Exec['ppa-augeas']}
apt::package { "python-software-properties": }

package { "ruby-augeas":
    provider => "gem",
    ensure => "installed",
    require => Apt::Package["libaugeas-dev"]
}

### Apt Config ###

define apt::key($keyid, $ensure, $keyserver = 'keyserver.ubuntu.com') {
  case $ensure {
    present: {
      exec { "Import $keyid to apt keystore":
        path        => '/bin:/usr/bin',
        environment => 'HOME=/root',
        command     => "gpg --keyserver $keyserver --recv-keys $keyid && gpg --export --armor $keyid | apt-key add -",
        user        => 'root',
        group       => 'root',
        unless      => "apt-key list | grep $keyid",
        logoutput   => on_failure,
      }
    }
    absent:  {
      exec { "Remove $keyid from apt keystore":
        path        => '/bin:/usr/bin',
        environment => 'HOME=/root',
        command     => "apt-key del $keyid",
        user        => 'root',
        group       => 'root',
        onlyif      => "apt-key list | grep $keyid",
      }
    }
    default: {
      fail "Invalid 'ensure' value '$ensure' for apt::key"
    }
  }
}

exec { "add-apt-repository 'ppa:raphink/augeas'":
    alias => "ppa-augeas",
    creates => "/etc/apt/sources.list.d/raphink-augeas-lucid.list",
    require => Apt::Package["python-software-properties"],
    user => 'root'
  }

# ToDo only if already in sources.list
exec { "add-apt-repository 'deb http://www.rabbitmq.com/debian/ testing main'":
    alias => "repo-rabbitmq",
    creates => "/etc/apt/sources.list.d/rabbitmq.list",
    require => Apt::Package["python-software-properties"],
    user => 'root'
}

### MongoDB ###

#TODO: use the apt module
apt::key { "mongodb-key":
    ensure => present,
    keyid => "7F0CEB10" 
}

file { "mongodb-apt-list":
    path => "/etc/apt/sources.list.d/mongodb-upstart-gen10.list",
    content => 'deb http://downloads-distro.mongodb.org/repo/ubuntu-upstart dist 10gen',
    owner => 'root',
    group => 'root',
    mode => '0644',
    require => Apt::Key["mongodb-key"],
    notify => Exec['apt-get update']
}

exec { "clone-mongodb-php-driver":
    command => "git clone git://github.com/mongodb/mongo-php-driver.git",
    cwd => "/opt",
    unless => "test -d /opt/mongo-php-driver/.git",
    require => [Apt::Package["make"], Apt::Package["git-core"]],
    #user => 'root'
}

exec { "checkout129-mongodb-php-driver":
    command => "git tag -l && git checkout 1.2.9",
    cwd => "/opt/mongo-php-driver",
    require => Exec["clone-mongodb-php-driver"],
    #user => 'root'
}

exec { "compile-mongodb-php-driver":
    command => "phpize && ./configure && make && sudo make install",
    cwd => "/opt/mongo-php-driver",
    unless => "test -f /opt/mongo-php-driver/modules/mongo.so",
    require => Exec["checkout129-mongodb-php-driver"],
    #user => 'root'
}

### Redis ###

include redis

exec { "clone-redis-php-driver":
    command => "git clone git://github.com/nicolasff/phpredis.git",
    cwd => "/opt",
    unless => "test -d /opt/phpredis/.git",
    require => [Apt::Package["make"], Apt::Package["git-core"]]
}

exec { "compile-redis-php-driver":
    command => "phpize && ./configure && make && sudo make install",
    cwd => "/opt/phpredis",
    unless =>  "test -f /opt/phpredis/modules/redis.so",
    require => Exec["clone-redis-php-driver"],
}


### RabbitMQ ###

class { 'rabbitmq::server':
    port              => '5672',
    delete_guest_user => true,
}

rabbitmq_user { "vumi":
    admin => false,
    password => "vumi",
    provider => "rabbitmqctl"
}

rabbitmq_vhost { "/develop":
    ensure => present,
    provider => "rabbitmqctl"
}

rabbitmq_user_permissions { "vumi@/develop":
    require => [Rabbitmq_user["vumi"],Rabbitmq_vhost["/develop"]], 
    configure_permission => ".*",
    read_permission => ".*",
    write_permission => ".*",
    provider => "rabbitmqctl"
}

### PHP.ini ###

#TODO: shouldn't we use some php ini module rather then augeas?
augeas { "add-extension-mongo":
  require => [
                Apt::Package["libaugeas-dev"], 
                Apt::Package["libaugeas0"],
                Exec["compile-mongodb-php-driver"]],
  context => "/files/etc/php5/apache2/php.ini",
  onlyif => "match PHP/extension[1] size == 0",
  changes => [
                "ins extension after PHP/#comment[747]",
                "set PHP/extension[1] mongo.so",
                "ins extension after PHP/extension[1]",
                "set PHP/extension[2] redis.so"]
}

### Vusion ###

file {
    "/var/vusion":
        ensure => "directory",
        owner => "vagrant",
}

exec { "clone-frontend-repository":
    command => "git clone git://github.com/texttochange/vusion-frontend.git",
    cwd => "/var/vusion",
    unless => "test -d /var/vusion/vusion-frontend/.git",
    require => [
        Package['git-core'],
        File['/var/vusion']
    ],
}

exec { "update-frontend-module":
    command => "git submodule init && git submodule update",
    cwd => "/var/vusion/vusion-frontend",
    unless => "test -d /var/vusion/vusion-frontend/app/Plugin/Mongodb/.git",
    require => Exec['clone-frontend-repository'],
}

# TODO: Exclude the file contained in the directories
file { "/var/vusion/vusion-frontend/app/tmp":
    ensure => directory, 
    recurse => true,
    mode => "0755",
    require => Exec["clone-frontend-repository"],
    owner => "www-data"
}

# TODO: include the backend as a submodule, rename the frontend as "Vusion"
exec { "clone-backend-repository":
    command => "git clone git://github.com/texttochange/vusion-backend.git",
    cwd => "/var/vusion",
    unless => "test -d /var/vusion/vusion-backend/.git",
    require => [
        Package['git-core'],
        File['/var/vusion']
    ],
}

exec { "setup-backend-virtualenv":
    command => "virtualenv ve && . ve/bin/activate && pip install -r requirements.pip",
    cwd => "/var/vusion/vusion-backend",
    unless => "test -d /var/vusion/vusion-backend/ve",
    require => Exec['clone-backend-repository'],
    user => 'vagrant',
    timeout => '0'
}

file {
    "/var/vusion/vusion-backend/logs":
        ensure => "directory",
}

exec { "start-backend":
    command => "bash -x ./startvm.sh",
    cwd => "/var/vusion/vusion-backend",
    user => 'vagrant',
    require => [Exec['setup-backend-virtualenv'],
                File['/var/vusion/vusion-backend/logs'],
                Rabbitmq_user_permissions['vumi@/develop']]
}
    

### Apache Configuration ###

include apache

class { 'apache::php': }

apache::vhost { "vusion":
    port => "80",
    docroot => "/var/vusion/vusion-frontend/app/webroot",
    serveraliases => ["*.localhost"],
    template => "apache/cake-default.conf.erb",
}

### MySQL Configuration ###

$root_password = "admin"
$cake_login = "cake"
$cake_password = "password" 

class { "mysql": }
apt::package { "php5-mysql": }

class { "mysql::server":
    config_hash => {'root_password' => $root_password} 
    }

#Need to move the /root/.my.cnf to ~/.my.cnf as the curent user is vagrant
mysql::db { "vusion":
    user => $cake_login,
    password => $cake_password,
    host => "localhost",
    grant => ["all"],
    sql => "/var/vusion/vusion-frontend/app/Test/data/mySQL/vusion.sql"
    }

