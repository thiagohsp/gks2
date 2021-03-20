import React, { HTMLAttributes, LinkHTMLAttributes } from 'react';
import { InertiaLink } from '@inertiajs/inertia-react';
import classNames from 'classnames';

interface PageLinkProps extends LinkHTMLAttributes<HTMLLinkElement> {
    active: boolean;
    label: string;
    url: string;
}

interface PageInactiveProps extends HTMLAttributes<HTMLDivElement> {
    label: string;
}

interface PaginationProps extends HTMLAttributes<HTMLDivElement> {
    links?: PageLinkProps[];
}

const PageLink: React.FC<PageLinkProps> = ({ active, label, url }) => {
    const className = classNames(
        [
            'my-2 mr-1',
            'px-4 py-3',
            'border border-solid border-gray-400 rounded',
            'text-sm',
            'hover:bg-gray-300',
            'focus:outline-none focus:border-indigo-700 focus:text-indigo-700'
        ],
        {
            'bg-gray-400': active
        }
    );
    return (
        <InertiaLink className={className} href={url}>
            <span dangerouslySetInnerHTML={{ __html: label }}></span>
        </InertiaLink>
    );
};

// Previous, if on first page
// Next, if on last page
// and dots, if exists (...)
const PageInactive: React.FC<PageInactiveProps> = ({ label }) => {
    const className = classNames(
        'my-2 mr-1 px-4 py-3 text-sm border rounded border-solid border-gray-300 text-gray'
    );
    return (
        <div className={className} dangerouslySetInnerHTML={{ __html: label }} />
    );
};

export const Pagination: React.FC<PaginationProps> = ({ links = [] }) => {
    // dont render, if there's only 1 page (previous, 1, next)
    if (links.length === 3) return null;
    return (
        <div className="flex flex-wrap mx-2">
            {links.map(({ active, label, url }) => {
                return url === null ? (
                    <PageInactive key={label} label={label} />
                ) : (
                        <PageLink key={label} label={label} active={active} url={url} />
                    );
            })}
        </div>
    );
};
