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
  "settings": null,
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
  "getSettings": function() {
    if (this.settings) {
      return this.settings;
    }

    var fs = this.dependencies.fs;

    var directory = this.folders.current;
    var settings_path = directory + "/singular.json";
    var exists = this.checkPath(settings_path);

    if (!exists) {
      throw "'singular.json' not found";
    }

    var contents = fs.readFileSync(settings_path);

    if (!contents) {
      throw "Error in 'singular.json'";
    }

    contents = JSON.parse(contents);

    if (!contents) {
      throw "Error in 'singular.json'";
    }

    this.settings = contents;

    return contents;
  },
  "getCurrentSettings": function() {
    var settings = this.getSettings();

    var mode = settings.mode;
    var modes = settings.modes

    return modes && mode && modes[mode] ? modes[mode] : null;
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
  "commands": {
    "help": {
      "description": "This help instructions.",
      "short": "h",
      "handler": function() {
        console.log(' _       __     __                             __                      __      \n| |     / /__  / /________  ____ ___  ___     / /_____     _________ _/ /_____ \n| | /| / / _ \\/ / ___/ __ \\/ __ `__ \\/ _ \\   / __/ __ \\   / ___/ __ `/ //_/ _ \\\n| |/ |/ /  __/ / /__/ /_/ / / / / / /  __/  / /_/ /_/ /  (__  ) /_/ / ,< /  __/\n|__/|__/\\___/_/\\___/\\____/_/ /_/ /_/\\___/   \\__/\\____/  /____/\\__,_/_/|_|\\___/ \n                                                                               ');

        var commands = this.commands;

        for (var command in commands) {
          var data = commands[command];
          var short = data.short;
          var description = data.description;

          console.log("\t" + command + " [" + short + "]\t" + description);
        }

        console.log("\n");
      }
    },
    "flush": {
      "description": "Clear Memcache (when enabled)",
      "short": "f",
      "handler": function() {
        var exec = this.dependencies.exec;

        var settings = this.getCurrentSettings();
        var cache = settings.cache;

        if (cache !== "\\Singular\\MemcacheCache") {
          console.log("MemCache not enabled.");
          return;
        }

        var server = settings.cache_server;

        if (!server) {
          console.log("Unknown cache server");
          return;
        }

        var port = settings.cache_port;

        if (!port) {
          console.log("Unknown cache port");
          return;
        }

        var command = "echo 'flush_all' | nc " + server + " " + port;

        var child_flush = exec(command, function(err, stdout, stderr) {
          console.log(stderr);
          console.log(stdout);
        });
      }
    },
    "update": {
      "description": "Update framework files",
      "short": "u",
      "handler": function() {
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
        });
      }
    },
    "create": {
      "description": "Create a new Singular app in the current directory",
      "short": "c",
      "handler": function() {
        var ncp = this.dependencies.ncp;

        this.clone_repository(function() {
          // Copy framework files
          sake.runCommand("update");

          var framework_base = sake.getPath(sake.folders.base);
          var current_path = sake.folders.current;

          // Copy base files
          ncp(framework_base, current_path, function (err) {
            if (err) {
              throw err;
            }
          });
        });
      }
    }
  },
  "runCommand": function(command) {
    if (this.commands[command] && this.commands[command].handler) {
      this.commands[command].handler.call(this);
    }
    else {
      var commands = this.commands;

      for (var c in commands) {
        var data = commands[c];
        var short = data.short;

        if (short === command) {
          data.handler.call(this);
          return;
        }
      }

      throw "Command '" + command + "' not found.";
    }
  },
  "processCommands": function() {
    var arguments = this.arguments;

    if (arguments.length == 0) {
      arguments = [ "help" ];
    }

    var arguments_length = arguments.length;

    for (var i = 0; i < arguments_length; i++) {
      var argument = arguments[i];

      this.runCommand(argument);
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
