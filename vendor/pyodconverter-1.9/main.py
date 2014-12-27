import uno
from com.sun.star.task import ErrorCodeIOException
from PyODConverter import (
    DocumentConverter, isfile,
    DocumentConversionException,
)
from sys import exit
from optparse import OptionParser

parser = OptionParser(usage="usage: python %prog [options] <input-file> <output-file>", version="%prog 1.5")
parser.add_option("-s", "--paper-size", default="A4", action="store", type="string", dest="paper_size", help="defines the paper size to converter that can be A3, A4, A5.")
parser.add_option("-o", "--paper-orientation", default="PORTRAIT", action="store", type="string", dest="paper_orientation", help="defines the paper orientation to converter that can be PORTRAIT or LANDSCAPE.")

(options, args) = parser.parse_args()

if len(args) != 2:
    parser.error("wrong number of arguments")

if not isfile(args[0]):
    print("No such input file: %s" % args[0])
    exit(1)

try:
    converter = DocumentConverter()
    converter.convert(args[0], args[1], options.paper_size, options.paper_orientation)
except DocumentConversionException as exception:
    print("ERROR! " + str(exception))
    exit(1)
except ErrorCodeIOException as exception:
    print("ERROR! ErrorCodeIOException %d" % exception.ErrCode)
    exit(1)
