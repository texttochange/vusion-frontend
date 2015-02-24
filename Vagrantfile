Vagrant::configure("2") do |config|
    config.vm.hostname = "vusion"
    config.vm.box = "vusionFine5"
    #config.vm.box_url = "file:d/project/ttc/vusion-deployment/vusion_lastest.box"
    config.vm.box_url = "file:d/project/ttc/vusion-frontend/vusion_cakephp_new.box"
    
    config.vm.network "forwarded_port", guest:80, host:4567   
    config.vm.network "forwarded_port", guest:9010, host:4568
    
    #to run the tests in your host env
    config.vm.network "forwarded_port", guest:27017, host:27017
    config.vm.network "forwarded_port", guest:6379, host:6379
    
    #to allow pushing messages to the default transports
    config.vm.network "forwarded_port", guest:2221, host:2221
    config.vm.network "forwarded_port", guest:2222, host:2223
    
    config.vm.network "private_network", ip:"10.11.12.13"
    # config.vm.synced_folder "app", "/var/vusion/app",  type:"nfs"
    # config.vm.synced_folder "lib", "/var/vusion/lib",  type:"nfs"
    #config.vm.synced_folder "composer", "/var/vusion/composer",  type:"nfs"
    # config.vm.synced_folder "backend/pip", "/var/vusion/backend/pip", type:"nfs"
    
    config.vm.synced_folder "app/Config", "/var/vusion/app/Config",  type:"nfs"
    config.vm.synced_folder "app/Controller", "/var/vusion/app/Controller",  type:"nfs"
    config.vm.synced_folder "app/Lib", "/var/vusion/app/Lib",  type:"nfs"
    config.vm.synced_folder "app/Locale", "/var/vusion/app/Locale",  type:"nfs"
    config.vm.synced_folder "app/Model", "/var/vusion/app/Model",  type:"nfs"
    config.vm.synced_folder "app/Test", "/var/vusion/app/Test",  type:"nfs"
    config.vm.synced_folder "app/tmp", "/var/vusion/app/tmp",  type:"nfs"
    config.vm.synced_folder "app/View", "/var/vusion/app/View",  type:"nfs"
    config.vm.synced_folder "app/webroot", "/var/vusion/app/webroot",  type:"nfs"
    
    config.vm.synced_folder "backend/vusion", "/var/vusion/backend/vusion", type:"nfs"
    config.vm.synced_folder "backend/transports", "/var/vusion/backend/transports", type:"nfs"
    config.vm.synced_folder "backend/middlewares", "/var/vusion/backend/middlewares", type:"nfs"
    config.vm.synced_folder "backend/components", "/var/vusion/backend/components", type:"nfs"
    config.vm.synced_folder "backend/tests", "/var/vusion/backend/tests", type:"nfs"
    config.vm.synced_folder "backend/dispatchers", "/var/vusion/backend/dispatchers", type:"nfs"
    config.vm.synced_folder "backend/scripts", "/var/vusion/backend/scripts", type:"nfs"
    config.vm.synced_folder "backend/etc", "/var/vusion/backend/etc", type:"nfs"

end
