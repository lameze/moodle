<?php

require_once(__DIR__ . '/../../../../behat/behat_base.php');

class behat_editor_tiny extends behat_base {

    /**
     * @param $params
     * @param $editorid
     * @param $value
     * @return void
     */
    public function set_editor_value($params, $editorid, $value) {
        $js = <<<EOF
            require(['editor_tiny/editor'], (editor) => {
                const instance = editor.getInstanceForElementId('${editorid}');
                instance.setContent('${value}');
                instance.undoManager.add();
            });
        EOF;
        behat_base::execute_script_in_session($params->getSession(), $js);
    }
}
