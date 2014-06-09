YUI().use('node', 'array', function(Y) {
    function update_events_list(changeSelect) {
        var moduleSelect = Y.one('#id_plugin');
        var eventSelect = Y.one('#id_event');
        var moduleId = moduleSelect.get('value');
        var selector = '\\' + moduleId + '\\';
        eventSelect.all('option').hide().removeClass('visible');
        eventSelect.all('option').each(function(node) {
            if (node.get('value').substring(0, selector.length) == selector) {
                node.show();
                node.addClass('visible');
            }
        });
        if (changeSelect) {
            eventSelect.one('.visible').set('selected', 'selected');
        }
    }
    Y.on('domready', function(){
        var moduleSelect = Y.one('#id_plugin');
        update_events_list();
        moduleSelect.on('change', function(e) {
            update_events_list(true);
        }, this);
    });
});