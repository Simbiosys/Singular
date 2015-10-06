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
    "ncp": require('ncp').ncp
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
    "base": "{HOME}/.singular/repo/Singular/src"
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
  //////////////////////////////////////////////////////////////////////////////
  //                          INIT .singular FOLDER
  //////////////////////////////////////////////////////////////////////////////
  "createDir": function(dir) {
    var fs = this.dependencies.fs;
    
    if (!fs.existsSync(dir)) {
      fs.mkdirSync(dir);
    }
  },
  "initFolder": function() {
    var singular_path = this.getPath(this.folders.singular);
    this.createDir(singular_path);

    var repo_path = this.getPath(this.folders.repo);
    this.createDir(repo_path);
  },
  "clone": function() {
    var exec = this.dependencies.exec;

    // Change working directory to .singular
    var repo_path = this.getPath(this.folders.repo);
    process.chdir(repo_path);

    var command = "git clone " + this.repository;

    var child_clone = exec(command, function(err, stdout, stderr) {
      console.log(stdout);
    });

    command = "git pull";

    var child_pull = exec(command, function(err, stdout, stderr) {
      console.log(stdout);
    });
  },
  "updateRepo": function() {
    this.initFolder();
    this.clone();
  },
  //////////////////////////////////////////////////////////////////////////////
  //                                 COMMANDS
  //////////////////////////////////////////////////////////////////////////////
  "help": function() {
    console.log('Welcome to sake');
  },
  "create": function() {
    var ncp = this.dependencies.ncp;

    this.updateRepo();

    var repo_path = this.getPath(this.folders.base);
    var current_path = this.folders.current;
    var base_path = current_path + "/base";

    this.createDir(base_path);

    // Copy Framework files
    ncp(repo_path, base_path, function (err) {
      if (err) {
        throw err;
      }
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
