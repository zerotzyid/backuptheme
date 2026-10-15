<!-- Import scripts. -->
@if(Auth::check())
  <script src="/extensions/darkpsyho/libraries/fetchStyle.js?1792029688"></script>
  <script src="/extensions/darkpsyho/libraries/currentPage.js?1792029688"></script>
  <script src="/extensions/darkpsyho/libraries/fetchServerId.js?1792029688"></script>
  <script src="/extensions/darkpsyho/libraries/statusOrb.js?1792029688"></script>
  <script src="/extensions/darkpsyho/libraries/customContextMenu.js?1792029688"></script>
  <script src="/extensions/darkpsyho/libraries/copyapi.js?1792029688"></script>
  <script src="/extensions/darkpsyho/libraries/assignElementIds.js?1792029688"></script>
  <script src="/extensions/darkpsyho/libraries/fileMode.js?1792029688"></script>
  <script src="/extensions/darkpsyho/libraries/insertAboveApp.js?1792029688"></script>
  <script src="/extensions/darkpsyho/libraries/sidebarMiddleClick.js?1792029688"></script>
  <script src="/extensions/darkpsyho/libraries/viewportVisibility.js?1792029688"></script>
  <script src="/extensions/darkpsyho/libraries/floatingWindows.js?1792029688"></script>
  @if($n_keyboard_shortcuts == "1")<script src="/extensions/darkpsyho/libraries/keybinds.js?1792029688"></script>@endif
  @if($n_keyboard_shortcuts == "1")<script src="/extensions/darkpsyho/libraries/keybindsModal.js?1792029688"></script>@endif
  @if($n_alert == "1")<script src="https://cdn.nebula.style/modules/marked/marked.min.js?1792029688" crossorigin="anonymous"></script>@endif
  <script src="/extensions/darkpsyho/libraries/assets/popper.min.js"></script>
  <script src="/extensions/darkpsyho/libraries/assets/tippy-bundle.umd.min.js"></script>
@endif
<script src="/extensions/darkpsyho/libraries/errorHandler.js?1792029688"></script>
<script src="/extensions/darkpsyho/libraries/locationchange.js?1792029688"></script>



<!-- Import stylesheets. -->
<style>
  @import url("/extensions/darkpsyho/libraries/statusOrb.css?1792029688");
  @import url("/extensions/darkpsyho/libraries/fixUserInterfaceBugs.css?1792029688");
  @import url("/extensions/darkpsyho/libraries/customContextMenu.css?1792029688");
  @import url("/extensions/darkpsyho/libraries/fileMode.css?1792029688");
  @import url("/extensions/darkpsyho/libraries/borderRadius.css?1792029688");
  @import url("/extensions/darkpsyho/libraries/extendedStyles.css?1792029688");
  @import url("/extensions/darkpsyho/libraries/tagStyling.css?1792029688");
  @import url("/extensions/darkpsyho/libraries/floatingWindows.css?1792029688");
  @import url("/extensions/darkpsyho/libraries/patterns.css?1792029688");
  @import url("/extensions/darkpsyho/libraries/animations.css?1792029688");
  @import url("/extensions/darkpsyho/libraries/darkpsyho.css");

  @if($n_server_list == "cards")
    @import url("/extensions/darkpsyho/libraries/serversCards.css?1792029688");
  @else
    @import url("/extensions/darkpsyho/libraries/serversList.css?1792029688");
  @endif

  @if($n_keyboard_shortcuts == "1")
    @import url("/extensions/darkpsyho/libraries/keybindsModal.css?1792029688");
  @endif

  @if($n_server_overview_graphs == "0")
    @import url("/extensions/darkpsyho/libraries/hideServerOverviewGraphs.css?1792029688");
  @endif

  @if($n_dashboard_transparency != "0")
    @import url("/extensions/darkpsyho/libraries/transparentUI.css?1792029688");
    @import url("/extensions/darkpsyho/libraries/extendedStylesTransparency.css?1792029688");
  @endif

  @if($n_sidebar_hover_tooltip == "1")
    @import url("/extensions/darkpsyho/libraries/sidebarTooltip.css?1792029688");
  @endif

  @if($n_website_links == "1")
    @import url("/extensions/darkpsyho/libraries/weblinks.css?1792029688");
  @endif

  @if($n_alert == "1")
    @import url("/extensions/darkpsyho/libraries/alert.css?1792029688");
  @endif

  @if($n_watermark_auth != "0")
    @import url("/extensions/darkpsyho/libraries/authWatermark.css?1792029688");
  @endif

  @if($blueprint->dbGet("settings", "recaptcha:enabled") == "true")
    @import url("/extensions/darkpsyho/libraries/recaptcha.css?1792029688");
  @endif

  @if(!Auth::check())
    @import url("/extensions/darkpsyho/libraries/hideRecaptcha.css?1792029688");
    @import url("/extensions/darkpsyho/libraries/extendedStylesAuth.css?1792029688");
  @endif

  <?php 
    /* Icon theme imports
    *
    * Nebula fetches icon themes from multiple sources: cdn.nebula.style and private.nebula.style. 
    * "private.nebula.style" is only used for assets we ("Emma (prpl.wtf)") had to purchase a commercial license for.
    */
  ?>
  @import url("https://cdn.nebula.style/icons/bootstrap/bootstrap-icons.css");
  @if($n_icon_fallback == "feather")                 @import url("https://cdn.nebula.style/icons/feather/feather-icons.css"); @endif
  @if($n_icon_fallback == "lucide")                  @import url("https://cdn.nebula.style/icons/lucide/lucide.css"); @endif
  @if($n_icon_fallback == "material")                @import url("https://cdn.nebula.style/icons/materialdesign/default/materialdesignicons.css"); @endif
  @if($n_icon_fallback == "material-light")          @import url("https://cdn.nebula.style/icons/materialdesign/light/materialdesignicons-light.css"); @endif
  @if($n_icon_fallback == "fontawesome")             @import url("https://cdn.nebula.style/icons/fontawesome/fontawesome.css"); @endif
  @if($n_icon_fallback == "eva-outline")             @import url("https://cdn.nebula.style/icons/eva/evaicons.css"); @endif
  @if($n_icon_fallback == "eva-solid")               @import url("https://cdn.nebula.style/icons/eva/evaicons.css"); @endif
  @if($n_icon_fallback == "remix-outline")           @import url("https://cdn.nebula.style/icons/remix/remixicon.css"); @endif
  @if($n_icon_fallback == "remix-solid")             @import url("https://cdn.nebula.style/icons/remix/remixicon.css"); @endif
  @if($n_icon_fallback == "tabler")                  @import url("https://cdn.nebula.style/icons/tabler/tabler.css"); @endif
  @if($n_icon_fallback == "octicons")                @import url("https://cdn.nebula.style/icons/octicons/octicons.css"); @endif
  @if($n_icon_fallback == "akar-icons")              @import url("https://cdn.nebula.style/icons/akar-icons/akar-icons.css"); @endif
  @if($n_icon_fallback == "hugeicons-solid")         @import url("https://private.nebula.style/icons/hugeicons/hugeicons-font.css"); @endif
  @if($n_icon_fallback == "hugeicons-stroke")        @import url("https://private.nebula.style/icons/hugeicons/hugeicons-font.css"); @endif
</style>