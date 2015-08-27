# drip
Identifies the file format and version using the UK National Archives PRONOM registry.

You can download the latest DROID Signature File at http://www.nationalarchives.gov.uk/aboutapps/pronom/droid-signature-files.htm

Basic use:

- require class scripts
- instanciate new drip, providing with a Droid Signature file path

- use drip::file to get the format identification for a file
- use drip::buffer to get the format from a stream contents
