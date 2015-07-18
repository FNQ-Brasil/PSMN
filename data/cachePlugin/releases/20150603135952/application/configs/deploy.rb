require 'capistrano/ext/multistage'
require 'rvm/capistrano'
require 'bundler/capistrano'

set :stages, %w{homolog production}

set :application, 'psmn'
set :user, 'psmn'

set :scm, :git
set :repository, 'ssh://git@200.201.182.126:2293/fnq/psmn.git'
set :deploy_to, "/home/#{user}/apps/#{application}"
set :deploy_via, :remote_cache

set :rvm_ruby_string, 'ruby-2.2.1@psmn'

set :normalize_asset_timestamps, false
set :use_sudo, false
set :pty, true
default_run_options[:pty] = true

set :format, :pretty
set :keep_releases, 5

after 'deploy', 'deploy:cleanup' # keep only the last 5 releases

after 'deploy:create_symlink', 'deploy:create_symlinks_for_external_resources'
after 'deploy:create_symlinks_for_external_resources', 'deploy:set_filesystem_permissions'

namespace :deploy do
  desc "create symlinks for 'premium-libs', 'files' and 'cover' at public folder"
  task :create_symlinks_for_external_resources, roles: :app do
    run "ln -s #{shared_path}/premium-libs/Fpdf #{current_path}/premium-libs/"
    run "ln -s #{shared_path}/premium-libs/jpgraph #{current_path}/premium-libs/"
    run "ln -s #{shared_path}/premium-libs/Zend #{current_path}/premium-libs/"

  	run "ln -s #{shared_path}/htdocs/files #{current_path}/htdocs/"
    run "ln -s #{shared_path}/htdocs/capa #{current_path}/htdocs/"
    run "ln -s #{shared_path}/htdocs/devolutives #{current_path}/htdocs/"
  end

  desc 'set required psmn permissions to directories and files'
  task :set_filesystem_permissions, roles: :app do
    run "chmod -R 775 #{current_path}/data/cachePlugin"
    run "chmod 775 #{current_path}/data/cacheAcl"
    run "chmod 775 #{current_path}/data/cacheFS"
    run "chmod -R 777 #{current_path}/data/logs"
  end
end

namespace :bundle do
  task :install do
    run <<-CMD
      cd #{release_path} &&
      bundle install --gemfile #{release_path}/Gemfile --path #{shared_path}/bundle --without development test
    CMD
  end
end
