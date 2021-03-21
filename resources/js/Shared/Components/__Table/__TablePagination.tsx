import React, { PropsWithChildren, ReactElement, useCallback } from 'react'
import { TableInstance } from 'react-table'

const rowsPerPageOptions = [5, 10, 25, 50]

// a bit of a type hack to keep OverridableComponent working as desired
export function TablePagination<T extends Record<string, unknown>>({
    instance,
}: PropsWithChildren<{ instance: TableInstance<T> }>): ReactElement | null {
    const {
        state: { pageIndex, pageSize, rowCount = instance.rows.length },
        canPreviousPage,
        canNextPage,
        pageOptions,
        pageCount,
        gotoPage,
        nextPage,
        previousPage,
        setPageSize
    } = instance

    const handleChangePage = useCallback(
        (event: React.MouseEvent<HTMLButtonElement, MouseEvent> | null, newPage: number) => {
            if (newPage === pageIndex + 1) {
                nextPage()
            } else if (newPage === pageIndex - 1) {
                previousPage()
            } else {
                gotoPage(newPage)
            }
        },
        [gotoPage, nextPage, pageIndex, previousPage]
    )

    const onChangeRowsPerPage = useCallback(
        (e) => {
            setPageSize(Number(e.target.value))
        },
        [setPageSize]
    )

    return rowCount ? (

        <>

            <div className="pagination">
                <button onClick={() => gotoPage(0)} disabled={!canPreviousPage}>
                    {'<<'}
                </button>{' '}
                <button onClick={() => previousPage()} disabled={!canPreviousPage}>
                    {'<'}
                </button>{' '}
                <button onClick={() => nextPage()} disabled={!canNextPage}>
                    {'>'}
                </button>{' '}
                <button onClick={() => gotoPage(pageCount - 1)} disabled={!canNextPage}>
                    {'>>'}
                </button>{' '}
                <span>
                    Page{' '}
                    <strong>
                        {pageIndex + 1} of {pageOptions.length}
                    </strong>{' '}
                </span>
                <span>
                    | Go to page:{' '}
                    <input
                        type="number"
                        defaultValue={pageIndex + 1}
                        onChange={e => {
                            const page = e.target.value ? Number(e.target.value) - 1 : 0
                            gotoPage(page)
                        }}
                        style={{ width: '100px' }}
                    />
                </span>{' '}
                <select
                    value={pageSize}
                    onChange={e => {
                        setPageSize(Number(e.target.value))
                    }}
                >
                    {[10, 20, 30, 40, 50].map(pageSize => (
                        <option key={pageSize} value={pageSize}>
                            Show {pageSize}
                        </option>
                    ))}
                </select>
            </div>
        </>) : null
}
