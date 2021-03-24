import React, { MouseEventHandler, PropsWithChildren, ReactElement, useMemo } from 'react';
import StateManager from 'react-select';
import { CellProps, Column, HeaderProps, Hooks, Row, TableInstance, TableOptions, usePagination, useRowSelect, useSortBy, useTable } from "react-table";
import { Pagination } from '../../Pagination';
import { HeaderCheckbox, RowCheckbox, TableContainer, Td, Th, Tr, TrHead } from "./styles";
import { TablePagination } from './TablePagination';
import processColumns from "./utils";


export interface TableProperties<T extends Record<string, unknown>> extends TableOptions<T> {
    name: string;
    hideHeaders?: boolean;
    onAdd?: (instance: TableInstance<T>) => MouseEventHandler
    onDelete?: (instance: TableInstance<T>) => MouseEventHandler
    onEdit?: (instance: TableInstance<T>) => MouseEventHandler
    onClick?: (row: Row<T>) => void;
    setSelectedRows?: (rows: Array<T>) => void
}


export function Table<T extends Record<string, unknown>>(props: PropsWithChildren<TableProperties<T>>): ReactElement {
    /* It's important that we're using React.useMemo here to ensure
     * that our data isn't recreated on every render. If we didn't use
     * React.useMemo, the table would think it was receiving new data on
     * every render and attempt to recalulate a lot of logic every single
     * time. Not cool!
     */

    const selectionHook = (hooks: Hooks<any>) => {
        hooks.allColumns.push((columns) => [
            // Let's make a column for selection
            {
                id: '_selector',
                disableResizing: false,
                disableGroupBy: true,
                minWidth: 5,
                width: 5,
                maxWidth: 5,
                // The header can use the table's getToggleAllRowsSelectedProps method
                // to render a checkbox
                Header: ({ getToggleAllRowsSelectedProps }: HeaderProps<any>) => (
                    <HeaderCheckbox width={5} {...getToggleAllRowsSelectedProps()} />
                ),
                // The cell can use the individual row's getToggleRowSelectedProps method
                // to the render a checkbox
                Cell: ({ row }: CellProps<any>) => <RowCheckbox width={5} {...row.getToggleRowSelectedProps()} />,
            },
            ...columns,
        ])
        hooks.useInstanceBeforeDimensions.push(({ headerGroups }) => {
            // fix the parent group of the selection button to not be resizable
            const selectionGroupHeader = headerGroups[0].headers[0]
            selectionGroupHeader.canResize = false
        })
    }

    const data = useMemo(() => props.data, [props.data]);
    const columns = useMemo(() => processColumns(props.columns, props.data), [
        props.columns,
        props.data,
    ]);

    const instance = useTable(
        // @ts-ignore
        { columns, data, disableSortBy: !props.enableSorting },
        useSortBy,
        usePagination,
        useRowSelect,
        selectionHook
    );

    const {
        getTableProps,
        getTableBodyProps,
        headerGroups,
        rows,
        prepareRow,
        page, // Instead of using 'rows', we'll use page,
        selectedFlatRows,
        state: { selectedRowIds },
    } = instance;

    React.useEffect(() => {
        if (props.setSelectedRows !== undefined) {
            const selectedRows = selectedFlatRows.map((item) => {
                return item.original;
            });
            props.setSelectedRows(selectedRows);
        }

    }, [props.setSelectedRows, selectedFlatRows])

    return (
        <>
            <TableContainer {...getTableProps()}>
                {!props.hideHeaders && (
                    <thead>
                        {
                            // Loop over the header rows
                            headerGroups.map((headerGroup: any) => (
                                // Apply the header row props
                                <TrHead {...headerGroup.getHeaderGroupProps()}>
                                    {
                                        // Loop over the headers in each row
                                        headerGroup.headers.map((column: any) => (
                                            // Apply the header cell props
                                            <Th
                                                {...column.getHeaderProps(column.getSortByToggleProps())}
                                            >
                                                {
                                                    // Render the header
                                                    column.render("Header")
                                                }
                                            </Th>
                                        ))
                                    }
                                </TrHead>
                            ))
                        }
                    </thead>
                )}

                <tbody {...getTableBodyProps()}>
                    {page.map((row, i) => {
                        prepareRow(row)
                        return (
                            <Tr {...row.getRowProps()}>
                                {row.cells.map(cell => {
                                    return <Td {...cell.getCellProps()}>{cell.render('Cell')}</Td>
                                })}
                            </Tr>
                        )
                    })}
                </tbody>
            </TableContainer>

            <TablePagination instance={instance} />
        </>
    );
}

Table.defaultProps = { enableSorting: false, hideHeaders: false };
