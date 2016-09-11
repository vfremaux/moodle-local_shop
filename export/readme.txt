Export section tries to architecture a practical organisation for 
cutting export/import responsibilities into assemblable pieces.

what we need : 

for export :
- A data definition, getting and assembling some data to export
- an output formatter getting data and formatting to some output to generate into a file or a stream.

for import :
- a parsing model to open a file and extract formatted data inside
- a data db feeder to distribute data in to the shop tables.

formats : 

format_excel : Produces excel data grids into a worksheet (or a workbook), take no assets in.
format_csv : Produces raw csv data. Takes no assets in.
format_mbz : Adopts a similar Moodle archive structure, storing data and assets in a Zip archive.