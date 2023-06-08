<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core\Views\Inputs;

class SettingsPanel extends \ADIOS\Core\Views\Input {
  var $folderTreeHtmlItems = [];

  public function render(string $panel = ''): string
  {
    $html = "
      <div id='{$this->uid}_form' class='adios ui Form'>
        <div class='adios ui Form table'>
    ";

    if (is_array($this->params['template']['tabs'])) {
      $tab_pages = [];
      foreach ($this->params['template']['tabs'] as $tab) {
        $tab_html = "";

        foreach ($tab['items'] as $item) {
          if (empty($item)) continue;

          if (is_string($item)) {
            $tab_html .= "
              <div class='adios ui Form subrow'>
                {$item}
              </div>
            ";
          } else {
            $item_html = "";

            if (isset($item['html'])) {
              $item_html = $item['html'];
            } else {
              $item_html = "
                <div class='adios ui Form form_input'>
                  ".$item['input']->render()."
                </div>
                ".(empty($item['description']) ? "" : "
                  <div class='adios ui Form form_description'>
                    {$item['description']}
                  </div>
                ")."
              ";
            }
            $tab_html .= "
              <div class='adios ui Form subrow'>
                <div class='adios ui Form form_title'>
                  {$item['title']}
                </div>
                {$item_html}
              </div>
            ";
          }
        }

        $tab_pages[] = [
          'title' => $tab['title'],
          'content' => $tab_html,
        ];
      }

      $html .= $this->addView('Tabs', ([
        'tabs' => $tab_pages,
        'height' => "calc(100vh - 16em)",
      ])->render();
    }

    if (is_array($this->params['template']['items'])) {
      foreach ($this->params['template']['items'] as $item) {
        if (empty($item)) continue;

        if (is_string($item)) {
          $html .= "
            <div class='adios ui Form subrow'>
              {$item}
            </div>
          ";
        } else {
          $html .= "
            <div class='adios ui Form subrow'>
              <div class='adios ui Form form_title'>
                {$item['title']}
              </div>
              <div class='adios ui Form form_input'>
                ".$item['input']->render()."
              </div>
              ".(empty($item['description']) ? "" : "
                <div class='adios ui Form form_description'>
                  {$item['description']}
                </div>
              ")."
            </div>
          ";
        }
      }
    }

    $html .= "
        </div>
      </div>
    ";


    return $html;
  }
}
