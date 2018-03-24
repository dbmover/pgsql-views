<?php

namespace Dbmover\Pgsql\Views;

use Dbmover\Views;

class Plugin extends Views\Plugin
{
    /**
     * @param string $sql
     * @return string
     */
    public function __invoke(string $sql) : string
    {
        $sql = parent::__invoke($sql);
        if (preg_match_all('@^CREATE\s+MATERIALIZED\s+VIEW.*?;$@ms', $sql, $views, PREG_SET_ORDER)) {
            foreach ($views as $view) {
                $sql = str_replace($view[0], '', $sql);
                $this->defer($view[0]);
            }
        }
        $stmt = $this->loader->getPdo()->prepare("SELECT matviewname FROM pg_matviews");
        $stmt->execute();
        while (false !== ($view = $stmt->fetchColumn())) {
            if (!$this->loader->shouldBeIgnored($view)) {
                $this->addOperation("DROP MATERIALIZED VIEW $view;");
            }
        }
        return $sql;
    }
}

