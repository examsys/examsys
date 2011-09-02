var script  = document.createElement('script');
script.src  = "/touchstone/tools/MathJax/MathJax.js";
script.type = 'text/javascript';

var config = 'MathJax.Hub.Config({' +
  'delayStartupUntil: "onload",' +
  'showProcessingMessages: false,' +
  'menuSettings: {zoom:"none"},' +
  'extensions: ["tex2jax.js", "TeX/AMSmath.js", "TeX/AMSsymbols.js", "TeX/boldsymbol.js", "TeX/autobold.js"],' +
  'jax: ["input/TeX","output/HTML-CSS"],' +
  'preRemoveClass: "MathJax_Preview",' +
  'tex2jax: {' +
    'showProcessingMessages: false,' +
    'displayMath: [],' +
    'inlineMath: [["$$","$$"],["[tex]","[/tex]"]],' +
    'inlineDelimiters: [["$$","$$"]],' +
    'preview: "none"' +
  '},' +
  '"HTML-CSS": { scale: 85,' +
                'showMathMenu: false,' +
                'availableFonts: ["STIX","TeX"],' +
                'preferredFont: "STIX",' +
                'minScaleAdjust: 50,' +
        '}' +
  '});';
  
if (window.opera) {
  script.innerHTML = config
} else {
  script.text = config
}  
document.getElementsByTagName('head')[0].appendChild(script);



