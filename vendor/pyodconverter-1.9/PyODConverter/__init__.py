#
# PyODConverter (Python OpenDocument Converter) v1.5 - 2013-01-07
#
# This script converts a document from one office format to another by
# connecting to an LibreOffice instance via Python-UNO bridge.
#
# Copyright (C) 2008-2013 Mirko Nasato
# Licensed under the GNU LGPL v2.1 - http://www.gnu.org/licenses/lgpl-2.1.html
# - or any later version.
#

__version__ = "1.9"

DEFAULT_OPENOFFICE_PORT = 2002

from numbers import Number
import datetime
from os.path import abspath, isfile, splitext

FAMILY_TEXT = "Text"
FAMILY_WEB = "Web"
FAMILY_SPREADSHEET = "Spreadsheet"
FAMILY_PRESENTATION = "Presentation"
FAMILY_DRAWING = "Drawing"

#---------------------#
# Configuration Start #
#---------------------#

'''
See http://www.openoffice.org/api/docs/common/ref/com/sun/star/view/PaperFormat.html
'''
PAPER_SIZE_MAP = {
    "A5": (14800,21000),
    "A4": (21000,29700),
    "A3": (29700,42000),
    "LETTER": (21590,27940),
    "LEGAL": (21590,35560),
    "TABLOID": (27900,43200)
}


'''
See http://wiki.services.openoffice.org/wiki/Framework/Article/Filter
most formats are auto-detected; only those requiring options are defined here
'''
IMPORT_FILTER_MAP = {
    "txt": {
        "FilterName": "Text (encoded)",
        "FilterOptions": "utf8"
    },
    "csv": {
        "FilterName": "Text - txt - csv (StarCalc)",
        "FilterOptions": "44,34,0"
    }
}

'''
The filter options to export PDF files can be viewed on URL below
http://wiki.openoffice.org/wiki/API/Tutorials/PDF_export#General_properties
'''
EXPORT_FILTER_MAP = {
    "pdf": {
        FAMILY_TEXT: {
            "FilterName": "writer_pdf_Export",
            "FilterData": {
                "IsSkipEmptyPages": True
            },
            "Overwrite": True
        },
        FAMILY_WEB: {
            "FilterName": "writer_web_pdf_Export",
            "FilterData": {
                "IsSkipEmptyPages": True
            },
            "Overwrite": True
        },
        FAMILY_SPREADSHEET: {
            "FilterName": "calc_pdf_Export",
            "FilterData": {
                "IsSkipEmptyPages": True
            },
            "Overwrite": True
        },
        FAMILY_PRESENTATION: {
            "FilterName": "impress_pdf_Export",
            "FilterData": {
                "IsSkipEmptyPages": True
            },
            "Overwrite": True
        },
        FAMILY_DRAWING: {
            "FilterName": "draw_pdf_Export",
            "FilterData": {
                "IsSkipEmptyPages": True
            },
            "Overwrite": True
        }
    },
    "html": {
        FAMILY_TEXT: {
            "FilterName": "HTML (StarWriter)",
            "Overwrite": True
        },
        FAMILY_SPREADSHEET: {
            "FilterName": "HTML (StarCalc)",
            "Overwrite": True
        },
        FAMILY_PRESENTATION: {
            "FilterName": "impress_html_Export",
            "Overwrite": True
        }
    },
    "odt": {
        FAMILY_TEXT: {
            "FilterName": "writer8",
            "Overwrite": True
        },
        FAMILY_WEB: {
            "FilterName": "writerweb8_writer",
            "Overwrite": True
        }
    },
    "doc": {
        FAMILY_TEXT: {
            "FilterName": "MS Word 97",
            "Overwrite": True
        }
    },
    "docx": {
        FAMILY_TEXT: {
            "FilterName": "MS Word 2007 XML",
            "Overwrite": True
        }
    },
    "rtf": {
        FAMILY_TEXT: {
            "FilterName": "Rich Text Format",
            "Overwrite": True
        }
    },
    "txt": {
        FAMILY_TEXT: {
            "FilterName": "Text",
            "FilterOptions": "utf8",
            "Overwrite": True
        }
    },
    "ods": {
        FAMILY_SPREADSHEET: {
            "FilterName": "calc8",
            "Overwrite": True
        }
    },
    "xls": {
        FAMILY_SPREADSHEET: {
            "FilterName": "MS Excel 97",
            "Overwrite": True
        }
    },
    "csv": {
        FAMILY_SPREADSHEET: {
            "FilterName": "Text - txt - csv (StarCalc)",
            "FilterOptions": "44,34,0",
            "Overwrite": True
        }
    },
    "odp": {
        FAMILY_PRESENTATION: {
            "FilterName": "impress8",
            "Overwrite": True
        }
    },
    "ppt": {
        FAMILY_PRESENTATION: {
            "FilterName": "MS PowerPoint 97",
            "Overwrite": True
        }
    },
    "pptx": {
        FAMILY_PRESENTATION: {
            "FilterName": "Impress MS PowerPoint 2007 XML",
            "Overwrite": True
        }
    },
    "swf": {
        FAMILY_DRAWING: {
            "FilterName": "draw_flash_Export",
            "Overwrite": True
        },
        FAMILY_PRESENTATION: {
            "FilterName": "impress_flash_Export",
            "Overwrite": True
        }
    },
    "png": {
        FAMILY_PRESENTATION: {
            "FilterName": "impress_png_Export",
            "Overwrite": True
        },
        FAMILY_DRAWING: {
            "FilterName": "draw_png_Export",
            "Overwrite": True
        }
    },
    "gif": {
        FAMILY_PRESENTATION: {
            "FilterName": "impress_gif_Export",
            "Overwrite": True
        },
        FAMILY_DRAWING: {
            "FilterName": "draw_gif_Export",
            "Overwrite": True
        }
    },
    "jpg": {
        FAMILY_PRESENTATION: {
            "FilterName": "impress_jpg_Export",
            "Overwrite": True
        },
        FAMILY_DRAWING: {
            "FilterName": "draw_jpg_Export",
            "Overwrite": True
        }
    }
}

PAGE_STYLE_OVERRIDE_PROPERTIES = {
    FAMILY_SPREADSHEET: {
        #--- Scale options: uncomment 1 of the 3 ---
        # a) 'Reduce / enlarge printout': 'Scaling factor'
        "PageScale": 100,
        # b) 'Fit print range(s) to width / height': 'Width in pages' and 'Height in pages'
        #"ScaleToPagesX": 1, "ScaleToPagesY": 1000,
        # c) 'Fit print range(s) on number of pages': 'Fit print range(s) on number of pages'
        #"ScaleToPages": 1,
        "PrintGrid": False
    }
}

IMAGES_MEDIA_TYPE = {
    "png": "image/png",
    "jpeg": "image/jpeg",
    "jpg": "image/jpeg",
    "gif": "image/gif"
}

WRITEABLE_DOCUMENT_PROPERTIES = ['Author', 'Description', 'Keywords',
                                 'Subject', 'Title']
#-------------------#
# Configuration End #
#-------------------#

class DocumentConversionException(Exception):

    def _get_message(self):
        return self._message

    def _set_message(self, message):
        self._message = message

    message = property(_get_message, _set_message)


class DocumentConverter:

    def __init__(self, listener=('localhost', DEFAULT_OPENOFFICE_PORT)):
        import uno
        from com.sun.star.connection import NoConnectException
        address, port = listener
        localContext = uno.getComponentContext()
        resolver = localContext.ServiceManager.createInstanceWithContext("com.sun.star.bridge.UnoUrlResolver", localContext)
        try:
            self.context = resolver.resolve("uno:socket,host={0},port={1};urp;StarOffice.ComponentContext".format(address, port))
        except NoConnectException:
            raise DocumentConversionException("failed to connect to LibreOffice on {0}:{1}".format(address, port))
        self.desktop = self.context.ServiceManager.createInstanceWithContext("com.sun.star.frame.Desktop", self.context)

    def convert(self, inputFile, outputFile,
                paperSize="A4", paperOrientation="PORTRAIT",
                data=None):
        import uno

        if not paperSize in PAPER_SIZE_MAP:
            raise Exception("The paper size given doesn't exist.")
        else:
            from com.sun.star.awt import Size
            paperSize = Size(*PAPER_SIZE_MAP[paperSize])


        from com.sun.star.view.PaperOrientation import PORTRAIT, LANDSCAPE
        #See http://www.openoffice.org/api/docs/common/ref/com/sun/star/view/PaperOrientation.html
        PAPER_ORIENTATION_MAP = {
            "PORTRAIT": PORTRAIT,
            "LANDSCAPE": LANDSCAPE
        }
        if not paperOrientation in PAPER_ORIENTATION_MAP:
            raise Exception("The paper orientation given doesn't exist.")
        else:
            inputExt = self._getFileExt(inputFile)
            if inputExt in ("ppt", "pptx", "opp"):
                paperOrientation = PAPER_ORIENTATION_MAP["LANDSCAPE"]
            else:
                paperOrientation = PAPER_ORIENTATION_MAP[paperOrientation]

        inputUrl = self._toFileUrl(inputFile)
        outputUrl = self._toFileUrl(outputFile)

        loadProperties = { "Hidden": True }

        inputExt = self._getFileExt(inputFile)
        outputExt = self._getFileExt(outputFile);

        if inputExt in IMPORT_FILTER_MAP:
            loadProperties.update(IMPORT_FILTER_MAP[inputExt])

        try:
            document = self.desktop.loadComponentFromURL(inputUrl, "_blank", 0,
                                            self._toProperties(loadProperties))
        except Exception as error:
            raise DocumentConversionException(str(error))
        try:
            document.refresh()
        except AttributeError:
            pass

        if data is not None:
            self._fillData(document, data)

        family = self._detectFamily(document)

        try:
            '''
            If you wish convert a document to an image, so each page needs be converted to a individual image.
            '''
            if outputExt in IMAGES_MEDIA_TYPE:

                have_pages = getattr(document, 'getDrawPages', None)
                if not have_pages:
                    raise DocumentConversionException("document doesn have pages")
                drawPages = document.getDrawPages()
                pagesTotal = drawPages.getCount()
                mediaType = IMAGES_MEDIA_TYPE[outputExt]
                fileBasename = self._getFileBasename(outputUrl)
                graphicExport = self.context.ServiceManager.createInstanceWithContext("com.sun.star.drawing.GraphicExportFilter", self.context)

                for pageIndex in range(pagesTotal):

                    page = drawPages.getByIndex(pageIndex)
                    fileName = "%s-%d.%s" % (fileBasename, pageIndex, outputExt)

                    graphicExport.setSourceDocument( page )

                    props = {
                        "MediaType": mediaType,
                        "URL": fileName
                    }

                    graphicExport.filter( self._toProperties( props ) )
            else:

                self._overridePageStyleProperties(document, family)

                storeProperties = self._getStoreProperties(document, outputExt)
                from com.sun.star.view.PaperFormat import USER
                printConfigs = {
                    'AllSheets': True,
                    'Size': paperSize,
                    'PaperFormat': USER,
                    'PaperOrientation': paperOrientation
                }

                document.setPrinter( self._toProperties( printConfigs ) )

                document.storeToURL(outputUrl, self._toProperties(storeProperties))
        finally:
            document.close(True)

    def _fillData(self, document, data):
        """Fill bookmarks and fields with data
        """
        try:
            document_properties = document.getDocumentProperties()
            for property_id in WRITEABLE_DOCUMENT_PROPERTIES:
                if property_id in data:
                    setattr(document_properties, property_id, data[property_id])

            bookmarks = document.getBookmarks()
            element_names = bookmarks.getElementNames()
            if element_names: # when no bookmark, we get a <ByteString ''>
                for name in element_names:
                    if name in data:
                        bookmark = bookmarks.getByName(name)
                        xfound = bookmark.getAnchor()
                        xfound.setString(data[name] or "")

            textfieldmasters = document.getTextFieldMasters()
            element_names = textfieldmasters.getElementNames()
            if element_names:
                for name in element_names:
                    key = name.split('.')[-1]
                    if key in data:
                        value = data[key]
                        if value is None:
                            value = ""
                        elif isinstance(value, datetime.date):
                            value = (value - datetime.date(1899, 12, 30)).days

                        textfieldmaster = textfieldmasters.getByName(name)
                        if isinstance(value, Number):
                            # with numbers we work with Value
                            textfieldmaster.setPropertyValue('Value', value)
                        else:
                            textfieldmaster.setPropertyValue('Content',
                                                             str(value))

                document.getTextFields().refresh()
        except AttributeError:  # xsl file don't have getBookmarks?
            pass

    def _overridePageStyleProperties(self, document, family):
        if family in PAGE_STYLE_OVERRIDE_PROPERTIES:
            styleFamilies = document.getStyleFamilies()
            if styleFamilies.hasByName('PageStyles'):
                properties = PAGE_STYLE_OVERRIDE_PROPERTIES[family]
                pageStyles = styleFamilies.getByName('PageStyles')
                for styleName in pageStyles.getElementNames():
                    pageStyle = pageStyles.getByName(styleName)
                    for name, value in properties.items():
                        pageStyle.setPropertyValue(name, value)

    def _getStoreProperties(self, document, outputExt):
        family = self._detectFamily(document)
        try:
            propertiesByFamily = EXPORT_FILTER_MAP[outputExt]
        except KeyError:
            raise DocumentConversionException("unknown output format: '%s'" % outputExt)
        try:
            return propertiesByFamily[family]
        except KeyError:
            raise DocumentConversionException("unsupported conversion: from '%s' to '%s'" % (family, outputExt))

    def _detectFamily(self, document):
        if document.supportsService("com.sun.star.text.WebDocument"):
            return FAMILY_WEB
        if document.supportsService("com.sun.star.text.GenericTextDocument"):
            # must be TextDocument or GlobalDocument
            return FAMILY_TEXT
        if document.supportsService("com.sun.star.sheet.SpreadsheetDocument"):
            return FAMILY_SPREADSHEET
        if document.supportsService("com.sun.star.presentation.PresentationDocument"):
            return FAMILY_PRESENTATION
        if document.supportsService("com.sun.star.drawing.DrawingDocument"):
            return FAMILY_DRAWING
        raise DocumentConversionException("unknown document family: %s" % document)

    def _getFileExt(self, path):
        ext = splitext(path)[1]
        if ext is not None:
            return ext[1:].lower()

    def _getFileBasename(self, path):
        name = splitext(path)[0]
        if name is not None:
            return name

    def _toFileUrl(self, path):
        import uno
        return uno.systemPathToFileUrl(abspath(path))

    def _toProperties(self, options):
        import uno
        from com.sun.star.beans import PropertyValue
        props = []
        for key in options:
            if isinstance(options[key], dict):
                prop = PropertyValue(key, 0, uno.Any("[]com.sun.star.beans.PropertyValue",
                                                     (self._toProperties(options[key]))), 0)
            else:
                prop = PropertyValue(key, 0, options[key], 0)
            props.append(prop)
        return tuple(props)

    def _dump(self, obj):
        for attr in dir(obj):
            print("obj.%s = %s\n" % (attr, getattr(obj, attr)))
