import React, { useEffect, useMemo, useRef, useState } from "react";
import { Table } from "../../Shared/Components/__Table";
import { Layout } from "../../Shared/Layout";
import { FiDownload } from 'react-icons/fi'
import { format, parseISO } from "date-fns";
import { Column } from "react-table";


type Bill = {
    id: string;
    bill_number: string;
    due_date: string;
    payment_date?: string;
    value: number;
    payment_value?: number;
    net_value?: number;
    link: string;
    invoice_id: string;
    account_id: string;
    batch_id: string;
    created_at: string;
    updated_at: string;

    invoice: {
        customer: {
            document_number: string,
            social_name: string,
        }
    }
}

interface IPageProps {
    data: Bill[];
}

const Index: React.FC<IPageProps> = (props) => {

    console.log(props.data)

    const columns: Array<Column<Bill>> = useMemo(() => [
        {
            Header: 'Número',
            accessor: 'bill_number',

        },
        {
            Header: 'Cliente',
            accessor: row => `${row.invoice.customer.document_number} - ${row.invoice.customer.social_name}`

        },
        {
            Header: 'Data de Vencimento',
            accessor: (d: any) => {
                return format(parseISO(d.due_date), "dd/MM/yyyy")
            }
        },
        {
            accessor: 'value',
            Cell: (props: any) => <div style={{ textAlign: "right" }}>{props.value.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })}</div>,
            Header: () => (
                <div style={{ textAlign: "right" }}>Valor</div>)

        },
        {
            Header: 'Ações',
            accessor: 'link',
            Cell: (props: any) =>
                <>
                    <a
                        target="_blank"
                        href={props.value}
                        className="bg-green-500 hover:bg-green-700 text-white text-xs font-bold mx-2 py-2 px-4 rounded-full inline-flex items-center">
                        <FiDownload size={16} color={"#fff"}></FiDownload>
                        <span className="ml-2">Download</span>
                    </a>
                </>
        },
    ], []);

    const title = 'Lista de Contas a Receber do Lote';

    return (
        <Layout title={title}>
            <div className="flex items-center justify-between mb-6">
                <h1 className="mb-1 text-2xl font-bold">{title}</h1>
            </div>

            <div className="bg-white rounded shadow">
                <Table<Bill>
                    //columns={columns}
                    name="batches-table"
                    columns={columns}
                    data={props.data}
                />
            </div>


        </Layout>
    );
};

export default Index;





