<?php
/**
 * Methods which can be used in (all) export renderer
 *
 */
namespace ZfcDatagrid\Renderer;

use ZfcDatagrid\Column;

abstract class AbstractExport extends AbstractRenderer
{

    protected $allowedColumnTypes = array(
        'ZfcDatagrid\Column\Type\DateTime',
        'ZfcDatagrid\Column\Type\Number',
        'ZfcDatagrid\Column\Type\PhpArray',
        'ZfcDatagrid\Column\Type\String'
    );

    protected $columnsToExport;

    /**
     * Decide which columns we want to display
     *
     * @return Column\AbstractColumn[]
     */
    protected function getColumnsToExport()
    {
        if (is_array($this->columnsToExport)) {
            return $this->columnsToExport;
        }

        $columnsToExport = array();
        foreach ($this->getColumns() as $column) {
            /* @var $column \ZfcDatagrid\Column\AbstractColumn */

            if (! $column instanceof Column\Action && $column->isHidden() === false && in_array(get_class($column->getType()), $this->allowedColumnTypes)) {
                $columnsToExport[] = $column;
            }
        }
        if (count($columnsToExport) === 0) {
            throw new \Exception('No columns to export available');
        }

        $this->columnsToExport = $columnsToExport;

        return $this->columnsToExport;
    }

    /**
     * Get the paper width in MM (milimeter)
     *
     * @return float
     */
    protected function getPaperWidth()
    {
        $optionsRenderer = $this->getOptionsRenderer();

        $papersize = $optionsRenderer['papersize'];
        $orientation = $optionsRenderer['orientation'];

        if (substr($papersize, 0, 1) != 'A') {
            throw new \Exception('Currently only "A" paper formats are supported!');
        }

        // calc from A0 to selected
        $divisor = substr($papersize, 1, 1);

        // A0 dimensions = 841 x 1189 mm
        $currentX = 841;
        $currentY = 1189;
        for ($i = 0; $i < $divisor; $i ++) {
            $tempY = $currentX;
            $tempX = floor($currentY / 2);

            $currentX = $tempX;
            $currentY = $tempY;
        }

        if ($orientation == 'landscape') {
            return $currentY;
        } else {
            return $currentX;
        }
    }

    /**
     * Get a valid filename to save
     * (WITHOUT the extension!)
     *
     * @return string
     */
    protected function getFilename()
    {
        $filenameParts = array();
        $filenameParts[] = date('Y-m-d_H-i-s');

        if ($this->getTitle() != '') {
            $title = $this->getTitle();
            $title = str_replace(' ', '_', $title);

            $filenameParts[] = preg_replace("/[^a-z0-9_-]+/i", "", $title);
        }

        return implode('_', $filenameParts);
    }
}
