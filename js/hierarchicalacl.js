window.JSONEditor.defaults.callbacks.template = {
  arrayIndex: function(jseditor, e) {
    console.log(jseditor, e);
    return jseditor.parent.key*1+1;
  }
};
