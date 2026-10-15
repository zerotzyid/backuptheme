<html>
  <head>
    <style>
      @import url('https://fonts.googleapis.com/css2?family=Inter&display=swap&version=500045');
      @import url("https://cdn.nebula.style/icons/bootstrap/bootstrap-icons.css");
    </style>
    <script src="/extensions/darkpsyho/libraries/errorHandler.js"></script>
  </head>
  <body>
    <div id="app"></div>
    <script>
      HandleDarkpsyhoError(`
        <code>${window.location.hostname}</code>
        utilizes Darkpsyho to bring users an optimal experience.
        This page contains information that might be useful for
        debugging certain problems administrators might encounter
        with modifications such as Darkpsyho.

        <div style="
          background-color: #241e2f;
          border-radius: 7px;
          padding: 10px;
          width: max-content;
        ">
          <p style="margin: 0">
            <code>
              {identifier} nebula <br>
              {name} Darkpsyho <br>
              {author} prplwtf <br>
              {version} 2.0-1 <br>
              {random} 20743 <br>
              {timestamp} 1792029688 <br>
              {mode} local <br>
              {target} beta-2026-08 <br>
              {root} /var/www/pterodactyl
            </code>
          </p>
        </div>
      `, ` `)
    </script>
  </body>
</html>