# coding: utf-8

from setuptools import setup, find_packages

setup(name='PyODConverter',
      version='1.9',
      author='PyODConverter contributors',
      author_email='github@require.pm',
      description='Python package to automate document conversions using LibreOffice/OpenOffice.org',
      long_description="PyODConverter (for Python OpenDocument Converter) is a Python package that automates office document conversions using LibreOffice or OpenOffice.org.",
      license='GPL v3',
      url='https://github.com/tyrannosaurus/pyodconverter',
      packages=find_packages(),
      classifiers=[
          'Intended Audience :: Developers',
          'Development Status :: 3 - Alpha',
          'Operating System :: OS Independent',
          'Programming Language :: Python',
          'Programming Language :: Python :: 2.7',
          'Programming Language :: Python :: 3.3',
      ],
      test_suite='PyODConverter.tests',
      platforms=['All'],
)
