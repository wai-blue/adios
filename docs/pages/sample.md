# Sample page

Learn how to create content by examples.

Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus vel sodales ex. Mauris a diam at leo dictum blandit. Sed fringilla enim at dui blandit, a maximus ante varius. Nam consectetur auctor dolor, quis finibus eros venenatis at. Fusce molestie vel odio aliquam hendrerit. Curabitur blandit a purus at porttitor. Nunc et erat eu odio tempor rhoncus fringilla id nulla. Fusce venenatis justo ac vehicula ornare. Quisque varius tellus id est dictum accumsan. Aenean in pulvinar neque, in luctus leo. Mauris ut ornare leo. Sed vitae ligula cursus, pulvinar eros a, sollicitudin ex.

## Text options

### Typography

Various font types: *italic*, **bold**.

Use VSCode plugin `Markdown` by **Yu Zhang** and `Ctrl+Shift+V` shortcut to easily preview page content.

### Simple list

  * item 1
  * item 2
  * item 3

### Numbered list

  1. item 1
  2. item 2
  3. item 3

### Internal link

[This is a link to an internal page of the book](sample)

### More examples

For more examples (e.g. links), see this [markdown documentation](https://www.markdownguide.org/). Be aware, that the library for parsing Markdown used in this guide might not support all Markdown features.

## Images

### Including images

To include your image, follow these steps:

  1. upload .png or .jpg file to book/content/images
    * filename of the image **MUST NOT** contain underscores (_)
  2. use this TWIG code in your page to add image:

<pre>
include 'components/image.twig'
with {'image': 'Lenna(testimage).png', 'caption': 'This is a sample image.'}
</pre>

### Sample image

{% include 'components/image.twig' with {'image': 'Lenna(testimage).png', 'caption': 'This is a sample image.'} %}

## Step-by-step wizard

### Example

{% include 'components/step-by-step.twig' with {'steps': {

  '1': {
    'title': 'Step 1',
    'description': '
This is **step** 1

You can use `markdown` syntax in step description.
    ',
    'example': 'You should do this.',
  },

  '2': {
    'title': 'Step 2',
    'description': '
This is **step** 2

You can use `markdown` syntax in step description.
    ',
    'example': 'You should do this.',
  }

}} %} {# step-by-step #}

## Tables

### Example
... to be done ...
