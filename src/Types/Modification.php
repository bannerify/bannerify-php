<?php

declare(strict_types=1);

namespace Bannerify\Types;

/**
 * Template modification
 * 
 * Generated from OpenAPI spec: Modification schema
 */
class Modification
{
    /**
     * @param string $name The layer name of the modification
     * @param string|null $text You can modify the text layer with this field
     * @param string|null $src The source image for the modification
     * @param string|null $color The color for the modification
     * @param bool|null $visible Set the visibility of the field
     * @param string|null $qrcode Modify the qrcode layer content with this field
     * @param string|null $barcode Modify the barcode layer content with this field
     * @param array|null $chart Update chart layer's data, follow chart.js data structure
     * @param float|null $star Star value
     * @param string|null $widthMode Table width mode (standard, adaptive)
     * @param string|null $heightMode Table height mode (standard, adaptive)
     * @param string|null $theme Table theme (NONE, DEFAULT, BRIGHT, SIMPLIFY, ARCO)
     * @param array|null $rows Table rows
     * @param array|null $columns Table columns
     */
    public function __construct(
        public string $name,
        public ?string $text = null,
        public ?string $src = null,
        public ?string $color = null,
        public ?bool $visible = null,
        public ?string $qrcode = null,
        public ?string $barcode = null,
        public ?array $chart = null,
        public ?float $star = null,
        public ?string $widthMode = null,
        public ?string $heightMode = null,
        public ?string $theme = null,
        public ?array $rows = null,
        public ?array $columns = null,
    ) {
    }

    /**
     * Create from array
     * 
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            text: $data['text'] ?? null,
            src: $data['src'] ?? null,
            color: $data['color'] ?? null,
            visible: $data['visible'] ?? null,
            qrcode: $data['qrcode'] ?? null,
            barcode: $data['barcode'] ?? null,
            chart: $data['chart'] ?? null,
            star: $data['star'] ?? null,
            widthMode: $data['widthMode'] ?? null,
            heightMode: $data['heightMode'] ?? null,
            theme: $data['theme'] ?? null,
            rows: $data['rows'] ?? null,
            columns: $data['columns'] ?? null,
        );
    }

    /**
     * Convert to array
     * 
     * @return array
     */
    public function toArray(): array
    {
        $data = ['name' => $this->name];

        if ($this->text !== null) {
            $data['text'] = $this->text;
        }
        if ($this->src !== null) {
            $data['src'] = $this->src;
        }
        if ($this->color !== null) {
            $data['color'] = $this->color;
        }
        if ($this->visible !== null) {
            $data['visible'] = $this->visible;
        }
        if ($this->qrcode !== null) {
            $data['qrcode'] = $this->qrcode;
        }
        if ($this->barcode !== null) {
            $data['barcode'] = $this->barcode;
        }
        if ($this->chart !== null) {
            $data['chart'] = $this->chart;
        }
        if ($this->star !== null) {
            $data['star'] = $this->star;
        }
        if ($this->widthMode !== null) {
            $data['widthMode'] = $this->widthMode;
        }
        if ($this->heightMode !== null) {
            $data['heightMode'] = $this->heightMode;
        }
        if ($this->theme !== null) {
            $data['theme'] = $this->theme;
        }
        if ($this->rows !== null) {
            $data['rows'] = $this->rows;
        }
        if ($this->columns !== null) {
            $data['columns'] = $this->columns;
        }

        return $data;
    }
}

