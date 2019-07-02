0.8.63-0
    * Introduction of skel/base in substitution of webApp as a base for all other skeletons
    * Original yeapf.db.ini has been moved from webApp to base
    slotEmptyImplementation.php has been moved from webApp to base
    * The same was done with body.php, yeapf_ticker.php, e_body.xml, graph.php, query.php, 
      ydbg.php, rest.php, configure.php, e_redirect.html, index.php, extractImage.php, 
      sse.php and  tasks.php
    * config2.php (from app-src) is now created in skel/base and not in skel/webApp
    * buildForm.php, f_sample.form, f_create_user.html, e_menu.html, develop* and menu.php 
    are now obsoletes.
    * skel/base/js/yloader.js ........<-----
