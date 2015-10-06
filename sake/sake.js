#! /usr/bin/env node

////////////////////////////////////////////////////////////////////////////////
//                                  SAKE
////////////////////////////////////////////////////////////////////////////////
var sake = {
  //////////////////////////////////////////////////////////////////////////////
  //                             DEPENDENCIES
  //////////////////////////////////////////////////////////////////////////////
  "dependencies": {
    "fs": require('fs'),
    "exec": require('child_process').exec,
    "ncp": require('ncp').ncp,
    "is_there": require("is-there")
  },
  //////////////////////////////////////////////////////////////////////////////
  //                                CONFIG
  //////////////////////////////////////////////////////////////////////////////
  "repository": "https://github.com/Simbiosys/Singular.git",
  "arguments": process.argv.slice(2),
  "folders": {
    "current": process.cwd(),
    "singular": "{HOME}/.singular",
    "repo": "{HOME}/.singular/repo",
    "repo_inner": "{HOME}/.singular/repo/Singular",
    "src": "{HOME}/.singular/repo/Singular/src",
    "base": "{HOME}/.singular/repo/Singular/base"
  },
  //////////////////////////////////////////////////////////////////////////////
  //                             AUX FUNCTIONS
  //////////////////////////////////////////////////////////////////////////////
  "getUserHome": function() {
    return process.env[(process.platform == 'win32') ? 'USERPROFILE' : 'HOME'];
  },
  "getPath": function(path) {
    return path.replace("{HOME}", this.getUserHome());
  },
  "createDir": function(dir) {
    var fs = this.dependencies.fs;

    if (!fs.existsSync(dir)) {
      fs.mkdirSync(dir);
    }
  },
  "checkPath": function(path) {
    var is_there = this.dependencies.is_there;

    return is_there(path);
  },
  //////////////////////////////////////////////////////////////////////////////
  //                          INIT .singular FOLDER
  //////////////////////////////////////////////////////////////////////////////
  "initFolder": function() {
    var singular_path = this.getPath(this.folders.singular);
    this.createDir(singular_path);

    var repo_path = this.getPath(this.folders.repo);
    this.createDir(repo_path);
  },
  "clone_singular": function(callback) {
    var exec = this.dependencies.exec;

    // Change working directory to .singular
    var repo_path = this.getPath(this.folders.repo);
    process.chdir(repo_path);

    // Check if repo already exists
    var src_path = this.getPath(this.folders.src);
    var path_exists = this.checkPath(src_path);

    if (!path_exists) {
      var command = "git clone " + this.repository;

      console.log("Cloning Singular repository");

      var child_clone = exec(command, function(err, stdout, stderr) {
        console.log(stderr);
        console.log(stdout);

        this.update_singular(callback);
      });
    }
    else {
      this.update_singular(callback);
    }
  },
  "update_singular": function(callback) {
    var exec = this.dependencies.exec;

    console.log("Updating Singular repository");

    var repo_inner_path = this.getPath(this.folders.repo_inner);
    process.chdir(repo_inner_path);

    command = "git pull";

    var child_pull = exec(command, function(err, stdout, stderr) {
      console.log(stderr);
      console.log(stdout);

      if (callback) {
        callback.call(this);
      }
    });
  },
  "clone_repository": function(callback) {
    this.initFolder();
    this.clone_singular(callback);
  },
  //////////////////////////////////////////////////////////////////////////////
  //                                 COMMANDS
  //////////////////////////////////////////////////////////////////////////////
  "help": function() {
    console.log('Welcome to sake');
  },
  "update": function() {
    this.clone_repository();
  },
  "create": function() {
    var ncp = this.dependencies.ncp;

    this.clone_repository(function() {
      console.log('Creating App files');

      var src_path = sake.getPath(sake.folders.src);
      var current_path = sake.folders.current;
      var base_path = current_path + "/base";

      sake.createDir(base_path);

      // Copy Framework files
      ncp(src_path, base_path, function (err) {
        if (err) {
          throw err;
        }
      });

      var framework_base = sake.getPath(sake.folders.base);

      // Copy base files
      ncp(framework_base, current_path, function (err) {
        if (err) {
          throw err;
        }
      });
    });
  },
  "processCommands": function() {
    var arguments = this.arguments;
    var arguments_length = arguments.length;

    for (var i = 0; i < arguments_length; i++) {
      var argument = arguments[i];

      if (this[argument]) {
        this[argument].call(this);
      }
      else {
        throw new Exception("Command '" + argument + "' not found.");
      }
    }
  },
  //////////////////////////////////////////////////////////////////////////////
  //                                   INIT
  //////////////////////////////////////////////////////////////////////////////
  "init": function() {
    this.processCommands();


  }
}

////////////////////////////////////////////////////////////////////////////////
//                                 RUN SAKE
////////////////////////////////////////////////////////////////////////////////
sake.init();
