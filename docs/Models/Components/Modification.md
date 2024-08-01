# Modification

A modification (aka override) to apply to the layer in image


## Fields

| Field                                                     | Type                                                      | Required                                                  | Description                                               | Example                                                   |
| --------------------------------------------------------- | --------------------------------------------------------- | --------------------------------------------------------- | --------------------------------------------------------- | --------------------------------------------------------- |
| `name`                                                    | *string*                                                  | :heavy_check_mark:                                        | The layer name of the modification                        | Text 1                                                    |
| `color`                                                   | *?string*                                                 | :heavy_minus_sign:                                        | The color for the modification                            | #FF0000                                                   |
| `src`                                                     | *?string*                                                 | :heavy_minus_sign:                                        | The source image for the modification                     | https://example.com/image.jpg                             |
| `text`                                                    | *?string*                                                 | :heavy_minus_sign:                                        | You can modify the text layer with this field             | Hello World                                               |
| `barcode`                                                 | *?string*                                                 | :heavy_minus_sign:                                        | Modify the barcode layer content with this field          | 1234567890                                                |
| `qrcode`                                                  | *?string*                                                 | :heavy_minus_sign:                                        | Modify the qrcode layer content with this field           | Some text                                                 |
| `chart`                                                   | array<string, *mixed*>                                    | :heavy_minus_sign:                                        | Update chart layer's data, follow chart.js data structure |                                                           |
| `visible`                                                 | *?bool*                                                   | :heavy_minus_sign:                                        | Set the visibility of the field                           | true                                                      |
| `star`                                                    | *?float*                                                  | :heavy_minus_sign:                                        | Star value                                                | 5                                                         |