import { PageProps } from "@inertiajs/inertia";
import { usePage } from "@inertiajs/inertia-react";
import React from "react";

interface ICustomer {
    id: string;
    document_number: string;
    social_name: string;
    adress_street: string;
    adress_number: string;
    adress_complement: string;
    adress_district: string;
    adress_zipcode: string;
    adress_city: string;
    adress_state: string;
    adress_country: string;
    email: string;
    customer_balance: number;
}

interface IInvoice {
    id: string;
    date: string;
    number: number;
    key: string;
    serie: string;
    cfop:string;
    value: number;
    balance: number;
    agent?: string;
    agent_2?: string;
    operation?: string;
    status: string;
    last_letter: string;
    customer: ICustomer

}

interface IPageProps {
    invoices: {
        data: IInvoice[];
    }
}

const Invoices: React.FC<IPageProps> = (props) => {

    const { data } = props.invoices;

    return (
        <div className="container">
            <table className="w-full whitespace-nowrap bordered">
                <tbody>
                    {data.map((item) => {
                    return (
                        <tr
                        key={item.id}
                        className="hover:bg-gray-100 focus-within:bg-gray-100"
                        >
                        <td className="border-t"> {item.number} </td>
                        <td className="border-t"> {item.date} </td>
                        <td className="border-t"> {item.serie} </td>
                        <td className="border-t"> {item.cfop} </td>
                        <td className="border-t"> {item.customer.social_name} </td>
                        <td className="border-t"> {item.value} </td>
                        <td className="border-t"> {item.balance} </td>
                        </tr>
                    );
                    })}
                </tbody>
            </table>
        </div>
    );
};

export default Invoices;
