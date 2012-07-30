Exec {
    path => ["/bin", "/usr/bin", "/usr/local/bin"],
    user => 'vagrant',
}


# Make sure packge index is updated
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
apt::package { "build-essential": }
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
apt::package { "redis-server": }

# Install Frontend packages
apt::package { "mongodb-10gen": require => File["mongodb-apt-list"] }
#apt::package { "mysql-server": }
apt::package { "php5": }
apt::package { "php5-dev": }
apt::package { "apache2": }
apt::package { "libapache2-mod-php5": }

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

/*# initial way of adding mongo to the apt list
exec { "add-apt-repository 'deb http://downloads-distro.mongodb.org/repo/ubuntu-upstart dist 10gen' && apt-get update && touch /etc/apt/sources.list.d/mongodb-upstart-gen10.list":
    alias => "mongodb_repository",
    creates => "/etc/apt/sources.list.d/mongodb-upstart-gen10.list",
    require => [ Apt::Key["mongodb_key"], Package["python-software-properties"]],
    user => 'root'
  }
*/

### MongoDB ###

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
    cwd => "/tmp",
    unless => "test -d /tmp/mongo-php-driver/.git",
    require => [Apt::Package["make"], Apt::Package["git-core"]]
}

exec { "checkout129-mongodb-php-driver":
    command => "git tag -l && git checkout 1.2.9",
    cwd => "/tmp/mongo-php-driver",
    require => Exec["clone-mongodb-php-driver"],
}

exec { "compile-mongodb-php-driver":
    command => "phpize && ./configure && make && sudo make install",
    cwd => "/tmp/mongo-php-driver",
    unless => "test -f /tmp/mongo-php-driver/modules/mongo.so",
    require => Exec["checkout129-mongodb-php-driver"],
}

### RabbitMQ ###

class { 'rabbitmq::server':
    port              => '5673',
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

file { "/var/vusion/vusion-frontend/app/tmp":
    ensure => directory, 
    recurse => true,
    mode => "0777",
    require => Exec["clone-frontend-repository"]
}

exec { "clone-backend-repository":
    command => "git clone git://github.com/texttochange/vusion-backend.git",
    cwd => "/var/vusion",
    unless => "test -d /var/vusion/vusion-backend/.git",
    require => [
        Package['git-core'],
        File['/var/vusion']
    ],
}

### Apache Configuration ###

include apache2

apache2::module {
    "rewrite": ensure => "present"
}

apache2::site {
    "000-default": ensure => "absent";
    "vusion": ensure => "present",
            require => File["/etc/apache2/sites-available/vusion"]    
}

file { "/etc/apache2/sites-available/vusion":
    source => "/var/vusion/vusion-frontend/puppet/files/vusion",
    require => Apt::Package["apache2"]
    }

$apache2_sites = "/etc/apache2/sites"
$apache2_mods = "/etc/apache2/mods"


class apache2 {    

    exec { "reload-apache2":
        command => "/etc/init.d/apache2 reload",
        refreshonly => true,
        user => 'root'
    }
    
    exec { "force-reload-apache2":
      command => "/etc/init.d/apache2 force-reload",
      refreshonly => true,
      user => 'root'
    }
   # Define an apache2 site. Place all site configs into
   # /etc/apache2/sites-available and en-/disable them with this type.
   #
   # You can add a custom require (string) if the site depends on packages
   # that aren't part of the default apache2 package. Because of the
   # package dependencies, apache2 will automagically be included.
   define site ( $ensure = 'present' ) {
      case $ensure {
         'present' : {
            exec { "/usr/sbin/a2ensite $name":
               unless => "/bin/readlink -e ${apache2_sites}-enabled/$name",
               notify => Exec["reload-apache2"],
               require => Apt::Package['apache2'],
               user => 'root'
            }
         }
         'absent' : {
            exec { "/usr/sbin/a2dissite $name":
               onlyif => "/bin/readlink -e ${apache2_sites}-enabled/$name",
               notify => Exec["reload-apache2"],
               require => Apt::Package['apache2'],
               user => 'root'
            }

         }
         default: { err ( "Unknown ensure value: '$ensure'" ) }
      }
   }

   # Define an apache2 module. Debian packages place the module config
   # into /etc/apache2/mods-available.
   #
   # You can add a custom require (string) if the module depends on 
   # packages that aren't part of the default apache2 package. Because of 
   # the package dependencies, apache2 will automagically be included.
   define module ( $ensure = 'present') {
      case $ensure {
         'present' : {
            exec { "/usr/sbin/a2enmod $name":
               unless => "/bin/readlink -e ${apache2_mods}-enabled/${name}.load",
               notify => Exec["force-reload-apache2"],
               require => Apt::Package["apache2"],
               user => 'root'
            }
         }
         'absent': {
            exec { "/usr/sbin/a2dismod $name":
               onlyif => "/bin/readlink -e ${apache2_mods}-enabled/${name}.load",
               notify => Exec["force-reload-apache2"],
               require => Apt::Package["apache2"],
               user => 'root'
            }
         }
         default: { err ( "Unknown ensure value: '$ensure'" ) }
      }
   }

   # Notify this when apache needs a reload. This is only needed when
   # sites are added or removed, since a full restart then would be
   # a waste of time. When the module-config changes, a force-reload is
   # needed.
 

   # We want to make sure that Apache2 is running.
   service { "apache2":
      ensure => running,
      hasstatus => true,
      hasrestart => true,
      require => Package["apache2"],
   }
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

