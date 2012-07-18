Exec {
    path => ["/bin", "/usr/bin", "/usr/local/bin"],
    user => 'vagrant',
}

# Make sure packge index is updated
exec { "apt-get update":
    command => "apt-get update",
    user => "root",
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
apt::package { "python-software-properties": }
apt::package { "mongodb-10gen": require => Exec["mongodb_repository"] }
apt::package { "mysql-server": }
apt::package { "php5": }
apt::package { "php5-dev": }
apt::package { "make": }
apt::package { "apache2": }
apt::package { "libapache2-mod-php5": }

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

apt::key { "mongodb_key":
    ensure => present,
    keyid => "7F0CEB10" 
}

exec { "sudo add-apt-repository 'deb http://downloads-distro.mongodb.org/repo/ubuntu-upstart dist 10gen' && sudo apt-get update":
    alias => "mongodb_repository",
    #creates => "/etc/apt/source.list.d/mongodb-upstart-gen10.list",
    require => [ Apt::Key["mongodb_key"], Package["python-software-properties"]]
  }

file {
    "/var/vusion":
        ensure => "directory",
        owner => "vagrant",
}

exec { "Clone git repository":
    command => "git clone git://github.com/texttochange/vusion-frontend.git",
    cwd => "/var/vusion",
    unless => "test -d /var/vusion/vusion-frontend/.git",
    subscribe => [
        Package['git-core'],
        File['/var/vusion']
    ],
}

apache2::site {
    "default": ensure => "absent";
    #"vusion": ensure => "present";
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
               subscribe => Exec['reload-apache2'],
               require => Package['apache2'],
            }
         }
         'absent' : {
            exec { "/usr/sbin/a2dissite $name":
               onlyif => "/bin/readlink -e ${apache2_sites}-enabled/$name",
               subscribe => Exec['reload-apache2'],
               require => Package['apache2'],
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
               notify => Exec["force-reload-apache2"],
               require => Package[$require],
            }
         }
         'absent': {
            exec { "/usr/sbin/a2dismod $name":
               onlyif => "/bin/readlink -e ${apache2_mods}-enabled/${name}.load",
               notify => Exec["force-reload-apache2"],
               require => Package["apache2"],
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
   }

   exec { "force-reload-apache2":
      command => "/etc/init.d/apache2 force-reload",
      refreshonly => true,
   }

   # We want to make sure that Apache2 is running.
   service { "apache2":
      ensure => running,
      hasstatus => true,
      hasrestart => true,
      require => Package["apache2"],
   }
}
