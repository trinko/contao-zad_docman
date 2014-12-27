## PyODConverter

PyODConverter (for Python OpenDocument Converter) is a Python package that
automates office document conversions using LibreOffice or OpenOffice.org.

## Important note

For more recent versions of LibreOffice (4.3.0+) you can use [pylokit](https://github.com/xrmx/pylokit).

## Setup

PyODConverter requires LibreOffice/OpenOffice.org to be running as a service
and listening on port (by default) 2002; this can be achieved e.g. by starting
it from the command line as
```
$ apt-get install unoconv
$ unoconv -l -v
```

## Usage

```python
from PyODConverter import DocumentConverter

listener = ('localhost', 2002)
converter = DocumentConverter(listener)

# LibreOffice automatically converts relative paths
converter.convert('kittens.docx', 'kittens.pdf')
# So, if you run this code from /opt/
# It'll be interpreted as this:
# converter.convert('/opt/kittens.docx', '/opt/kittens.pdf')
# That's all.

# If you want some bookmarks or user fields to be filled by custom data on
# generated document, you can give this data to converter like this :
converter.convert('/opt/kittens.docx', '/opt/kittens.pdf',
                  data={'age': 4, 
                        'name': 'Felix',
                        'birthdate': datetime.date(2010, 12, 24)},
                  )
# (this is not available in command line)

```

## ChangeLog

v1.9 - Unreleased
* Tests pass via setup.py command

v1.8 - 2014-06-28
* Tests pass under buildout context
* We can fill bookmarks, document properties and user fields of generated document
  with custom data given as a parameter of converter.
* Avoid border effects of uno import on python import system

v1.7 - 2013-11-01

* Python 3.3+ support
* Add default arguments to paperSize and paperOrientation parameters

v1.6 - 2013-06-05

* Fix support to print all sheets
* Fix parameters to initialize SOffice service

v1.5 - 2013-01-07

* Adding method to be able to get file base name
* Improvement files export from Presentation to Images. Now for each
  slide, an image will be created.

v1.4 - 2013-01-03

* Improvement the toProperties method to be able add array Uno properties
* Adding the Overwrite and IsSkipEmptyPages options.
* Update the README.

v1.3 - 2013-01-02

* Adding new docx format support.
* Adding paper size and orientation capable.
* Updated the options parser.
* Updated contributors.

v1.2 - 2012-03-10

* Changed default port to 2002
* Moved to GitHub

v1.1 - 2009-11-14

* Fixed HTML import issues by adding FAMILY\_WEB
* Support for specifying input formats and options
* Support for passing filter options to output formats
* Added CSV and TXT as input and output formats
* Support for overriding Page Style properties, especially useful for specifying
  how spreadsheets should fit into pages when exporting to PDF

v1.0.0 - 2008-05-05

* Let OOo determine the input document type, rather than using the file
  extension. This means all OOo-supported input types should now be accepted
  without any additional configuration.

## Contributors ##

* __mirkonasato__ <mirko.nasato@gmail.com>
* __marcelaraujo__ <admin@marcelaraujo.me>
* __Thomas Desvenain__ <thomas.desvenain@gmail.com>
