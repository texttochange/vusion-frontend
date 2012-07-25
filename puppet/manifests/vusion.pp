Exec {
    path => ["/bin", "/usr/bin", "/usr/local/bin"],
    user => 'vagrant',
}

# Make sure packge index is updated
exec { "apt-get update":
    command => "apt-get update",
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
apt::package { "build-essential": ensure => "11.4build1" }
apt::package { "python": ensure => "2.6.5-0ubuntu1" }
apt::package { "python-dev": ensure => "2.6.5-0ubuntu1" }
apt::package { "python-setuptools": ensure => "0.6.10-4ubuntu1" }
apt::package { "python-pip": ensure => "0.3.1-1ubuntu2" }
apt::package { "python-virtualenv": ensure => "1.4.5-1ubuntu1" }
apt::package { "rabbitmq-server": ensure => "1.7.2-1ubuntu1" }
apt::package { "git-core": ensure => "1:1.7.0.4-1ubuntu0.2" }
apt::package { "openjdk-6-jre-headless": }
apt::package { "libcurl3": ensure => "7.19.7-1ubuntu1.1" }
apt::package { "libcurl4-openssl-dev": ensure => "7.19.7-1ubuntu1.1" }
apt::package { "redis-server": ensure => "2:1.2.0-1" }

# Install Frontend packages
apt::package { "mongodb-10gen": require => File["mongodb-apt-list"] }
apt::package { "mysql-server": }
apt::package { "php5": }
apt::package { "php5-dev": }
apt::package { "apache2": }
apt::package { "libapache2-mod-php5": }

# Install packatge necessary for installation (to be removed at the end)
apt::package { "make": }
apt::package { "augeas-tools": }
apt::package { "libaugeas-dev": }
apt::package { "libaugeas-ruby": }
apt::package { "libaugeas0": }
apt::package { "augeas-lenses": }

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

apt::key { "mongodb-key":
    ensure => present,
    keyid => "7F0CEB10" 
}
/*# initial way of adding mongo to the apt list
apt::package { "python-software-properties": }
exec { "add-apt-repository 'deb http://downloads-distro.mongodb.org/repo/ubuntu-upstart dist 10gen' && apt-get update && touch /etc/apt/sources.list.d/mongodb-upstart-gen10.list":
    alias => "mongodb_repository",
    creates => "/etc/apt/sources.list.d/mongodb-upstart-gen10.list",
    require => [ Apt::Key["mongodb_key"], Package["python-software-properties"]],
    user => 'root'
  }
*/

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
    command => "git checkout 1.2.9",
    cwd => "/tmp/mongo-php-driver",
    require => Exec["clone-mongodb-php-driver"],
}

exec { "compile-mongodb-php-driver":
    command => "phpize && ./configure && make && sudo make install",
    cwd => "/tmp/mongo-php-driver",
    unless => "test -f /tmp/mongo-php-driver/modules/mongo.so",
    require => Exec["checkout129-mongodb-php-driver"],
}

augeas { "add-extension-mongo":
  require => [
                Apt::Package["augeas-tools"], 
                Apt::Package["libaugeas-dev"], 
                Apt::Package["libaugeas-ruby"],
                Apt::Package["libaugeas0"],
                Exec["compile-mongodb-php-driver"]],
  context => "/etc/php3/apache2/php.ini",
  changes => "set extension mongo.so"
}

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

file { "/var/vusion/vusion-frontend/app/temp":
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

apache2::site {
    "default": ensure => "absent";
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
               #notify => Exec['reload-apache2'],
               require => Package['apache2'],
               user => 'root'
            }
         }
         'absent' : {
            exec { "sudo /usr/sbin/a2dissite $name":
               onlyif => "/bin/readlink -e ${apache2_sites}-enabled/$name",
               #notify => Exec['reload-apache2'],
               require => Package['apache2'],
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
   define module ( $ensure = 'present', $require = 'apache2' ) {
      case $ensure {
         'present' : {
            exec { "/usr/sbin/a2enmod $name":
               unless => "/bin/readlink -e ${apache2_mods}-enabled/${name}.load",
               #notify => Exec["force-reload-apache2"],
               require => Package[$require],
               user => 'root'
            }
         }
         'absent': {
            exec { "/usr/sbin/a2dismod $name":
               onlyif => "/bin/readlink -e ${apache2_mods}-enabled/${name}.load",
               #notify => Exec["force-reload-apache2"],
               require => Package["apache2"],
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

   # We want to make sure that Apache2 is running.
   service { "apache2":
      ensure => running,
      hasstatus => true,
      hasrestart => true,
      require => Package["apache2"],
      user => 'root'
   }
}
